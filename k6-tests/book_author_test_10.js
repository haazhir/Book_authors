import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = { vus: 10, duration: '30s' };

export default function () {
    let res = http.get('https://authorbook.hazhir-ahmed.net/api/books.php');
    check(res, { 'GET books 200': (r) => r.status === 200 });
    res = http.get('https://authorbook.hazhir-ahmed.net/api/authors.php');
    check(res, { 'GET authors 200': (r) => r.status === 200 });
    res = http.post('https://authorbook.hazhir-ahmed.net/api/books.php', JSON.stringify({
        title: `Stress Book ${Math.random()}`, author_id: 1, publication_year: 2024
    }), { headers: { 'Content-Type': 'application/json' } });
    check(res, { 'POST book 201': (r) => r.status === 201 });
    res = http.post('https://authorbook.hazhir-ahmed.net/api/authors.php', JSON.stringify({
        name: `Stress Author ${Math.random()}`, bio: 'Auto-generated bio'
    }), { headers: { 'Content-Type': 'application/json' } });
    check(res, { 'POST author 201': (r) => r.status === 201 });
    res = http.put('https://authorbook.hazhir-ahmed.net/api/books.php?id=1', JSON.stringify({
        title: 'Updated Stress Book', author_id: 1, publication_year: 2024
    }), { headers: { 'Content-Type': 'application/json' } });
    check(res, { 'PUT book 200/404': (r) => r.status === 200 || r.status === 404 });
    res = http.put('https://authorbook.hazhir-ahmed.net/api/authors.php?id=1', JSON.stringify({
        name: 'Updated Stress Author', bio: 'Updated bio'
    }), { headers: { 'Content-Type': 'application/json' } });
    check(res, { 'PUT author 200/404': (r) => r.status === 200 || r.status === 404 });
    res = http.del('https://authorbook.hazhir-ahmed.net/api/books.php?id=1');
    check(res, { 'DELETE book 200/404': (r) => r.status === 200 || r.status === 404 });
    res = http.del('https://authorbook.hazhir-ahmed.net/api/authors.php?id=1');
    check(res, { 'DELETE author 200/404': (r) => r.status === 200 || r.status === 404 });
    sleep(1);
}
