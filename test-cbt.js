// Smoke:  k6 run test-cbt.js
// Stress: k6 run -e PROFILE=stress -e USER_PASSWORD=password123 test-cbt.js
// The stress profile ramps through 10, 50, then 100 VUs. Local seeded accounts
// are siswa1@cbt.com through siswa100@cbt.com; use dedicated test data only.

import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter } from 'k6/metrics';

const startFailures = new Counter('start_exam_failures');

const BASE_URL = (__ENV.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const PROFILE = (__ENV.PROFILE || 'smoke').toLowerCase();
const STRESS_PROFILE = PROFILE === 'stress';
const VUS = Number(__ENV.VUS || (STRESS_PROFILE ? 100 : 2));
const DURATION = __ENV.DURATION || '30s';
const THINK_TIME = Number(__ENV.THINK_TIME || (STRESS_PROFILE ? 2 : 0.2));
const P95_TARGET_MS = Number(__ENV.P95_TARGET_MS || 1000);
const USER_EMAILS = (__ENV.USER_EMAILS || Array.from({ length: VUS }, (_, index) => `siswa${index + 1}@cbt.com`).join(',')).split(',').map((email) => email.trim()).filter(Boolean);
const USER_PASSWORD = __ENV.USER_PASSWORD || 'password123';
const EVENT_ID = __ENV.EVENT_ID || '1';
const TEST_ID = __ENV.TEST_ID || '1';
const QUESTION_ID = __ENV.QUESTION_ID || '1';
const CHOICE_ID = __ENV.CHOICE_ID || '1';

if (!Number.isInteger(VUS) || VUS < 1) {
    throw new Error('VUS harus berupa bilangan bulat minimal 1.');
}

if (!['smoke', 'stress'].includes(PROFILE)) {
    throw new Error('PROFILE harus bernilai smoke atau stress.');
}

if (!__ENV.USER_EMAILS && VUS > 100) {
    throw new Error('Seeder lokal menyediakan 100 akun siswa; isi USER_EMAILS dengan akun tambahan atau turunkan VUS.');
}

if (new Set(USER_EMAILS).size < VUS) {
    throw new Error(`Perlu minimal ${VUS} email peserta yang unik; USER_EMAILS berisi ${new Set(USER_EMAILS).size}.`);
}

if (!USER_PASSWORD || !EVENT_ID || !TEST_ID || !QUESTION_ID || !CHOICE_ID) {
    throw new Error('Isi USER_PASSWORD, EVENT_ID, TEST_ID, QUESTION_ID, dan CHOICE_ID.');
}

export const options = {
    scenarios: {
        students: STRESS_PROFILE
            ? {
                executor: 'ramping-vus',
                startVUs: 0,
                stages: [
                    { duration: '30s', target: Math.min(10, VUS) },
                    { duration: '60s', target: Math.min(50, VUS) },
                    { duration: '60s', target: VUS },
                    { duration: '30s', target: 0 },
                ],
                gracefulRampDown: '10s',
            }
            : {
                executor: 'constant-vus',
                vus: VUS,
                duration: DURATION,
                gracefulStop: '10s',
            },
    },
    thresholds: {
        http_req_failed: ['rate<0.01'],
        'http_req_duration{endpoint:save_answer}': [`p(95)<${P95_TARGET_MS}`],
        start_exam_failures: ['count<1'],
    },
    noCookiesReset: true,
};

const params = (endpoint, csrfToken, referer = `${BASE_URL}/login`) => ({
    headers: {
        Accept: 'text/html, application/xhtml+xml',
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(csrfToken),
        Referer: referer,
    },
    tags: { endpoint },
});

function formBody(fields) {
    return Object.entries(fields)
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(String(value))}`)
        .join('&');
}

let vuReady = false;
let vuFailed = false;
let csrfToken = '';
let sessionUrl = '';

function prepareVu() {
    const email = USER_EMAILS[(__VU - 1) % USER_EMAILS.length];

    const loginPage = http.get(`${BASE_URL}/login`, { tags: { endpoint: 'login_page' } });
    check(loginPage, { 'login page loaded': (response) => response.status === 200 });

    const xsrfCookie = loginPage.cookies['XSRF-TOKEN']?.[0]?.value;
    if (!xsrfCookie) {
        throw new Error('Cookie XSRF-TOKEN tidak ditemukan dari halaman login.');
    }
    csrfToken = xsrfCookie;

    const loginResponse = http.post(
        `${BASE_URL}/login`,
        formBody({ email, password: USER_PASSWORD }),
        { ...params('login', csrfToken), redirects: 0 },
    );

    csrfToken = loginResponse.cookies['XSRF-TOKEN']?.[0]?.value || csrfToken;

    const loginRedirect = loginResponse.headers.Location || loginResponse.headers.location || '';
    if (loginResponse.status !== 302 || !loginRedirect.includes('/dashboard')) {
        throw new Error(`Login gagal untuk VU ${__VU}; periksa akun pada USER_EMAILS dan USER_PASSWORD.`);
    }

    const examsResponse = http.get(`${BASE_URL}/student/exams`, {
        headers: { Accept: 'text/html, application/xhtml+xml' },
        tags: { endpoint: 'student_exams' },
    });

    if (examsResponse.status !== 200 || !examsResponse.url.includes('/student/exams')) {
        throw new Error(`Akun VU ${__VU} tidak berhasil mengakses daftar ujian siswa.`);
    }

    sessionUrl = `${BASE_URL}/student/events/${EVENT_ID}/tests/${TEST_ID}/session`;
    const startResponse = http.post(
        `${BASE_URL}/student/events/${EVENT_ID}/tests/${TEST_ID}/start`,
        '',
        { ...params('start_exam', csrfToken, `${BASE_URL}/student/events/${EVENT_ID}/tests/${TEST_ID}/lobby`), redirects: 0 },
    );

    const startRedirect = startResponse.headers.Location || startResponse.headers.location || '';
    const started = (startResponse.status === 302 || startResponse.status === 303)
        && startRedirect.includes('/session');
    check(startResponse, {
        'exam start redirected to session': () => started,
    });

    if (!started) {
        startFailures.add(1);
        const responseBody = (startResponse.body || '').replace(/\s+/g, ' ').slice(0, 300);
        const reason = startRedirect.includes('/result')
            ? 'sesi akun ini sudah selesai; gunakan peserta baru atau reset sesi pada database testing'
            : `periksa EVENT_ID=${EVENT_ID}, TEST_ID=${TEST_ID}, keanggotaan event, status/waktu event, dan sesi peserta`;
        console.error(
            `VU ${__VU}: gagal memulai ujian. status=${startResponse.status}, `
            + `location=${startRedirect || '(tidak ada)'}, body=${responseBody || '(kosong)'}. `
            + reason + '.'
        );
        return false;
    }

    vuReady = true;
    return true;
}

export default function () {
    if (vuFailed) {
        // Do not busy-loop a VU after its setup failed.
        sleep(1);
        return;
    }

    if (!vuReady && !prepareVu()) {
        // Avoid retrying the same failed start request on every iteration.
        vuFailed = true;
        return;
    }

    const answerResponse = http.patch(
        `${BASE_URL}/student/events/${EVENT_ID}/tests/${TEST_ID}/questions/${QUESTION_ID}/answer`,
        formBody({ choice_id: CHOICE_ID, is_doubtful: __ITER % 2 === 0 ? 1 : 0 }),
        { ...params('save_answer', csrfToken, sessionUrl), redirects: 0 },
    );

    check(answerResponse, {
        'answer saved (redirect response)': (response) => response.status === 302 || response.status === 303,
    });

    if (THINK_TIME > 0) {
        sleep(THINK_TIME);
    }
}
