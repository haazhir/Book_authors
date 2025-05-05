import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
    scenarios: {
        test_10_users: {
            executor: 'constant-vus',
            vus: 10,
            duration: '30s',
            exec: 'testApi',
        },
        test_50_users: {
            executor: 'constant-vus',
            vus: 50,
            duration: '30s',
            startTime: '35s',
            exec: 'testApi',
        },
        test_100_users: {
            executor: 'constant-vus',
            vus: 100,
            duration: '30s',
            startTime: '70s',
            exec: 'testApi',
        },
    },
};

export function testApi() {
    const res = http.get('https://authorbook.hazhir-ahmed.net/api/authors.php');
    check(res, {
        'status is 200': (r) => r.status === 200,
    });
    sleep(1);
}
