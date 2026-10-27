<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# hotel_management_kitchen

## Progressive web app

The hotel management system can be installed from a supported browser using the **Install app** button. On iPhone and iPad, use the browser's **Add to Home Screen** option.

The service worker keeps the app icon, offline page, and static interface assets available and shows an offline message when a page cannot be reached. Hotel records, sign-in, and form submissions still require an internet connection; private pages and form data are not stored for offline use. Install and service worker features require HTTPS, except when running on localhost.

## Public lodge website

The public website uses the existing lodge, room, guest, and booking records. It adds these public routes: `/`, `/rooms-and-rates`, `/facilities`, `/gallery`, `/about`, `/contact`, `/book`, and `/sitemap.xml`. Public booking submissions are saved as **Pending** requests without assigning a room or taking payment. Staff confirm availability and final terms through the existing booking workflow.

### Setup

1. Apply the additive website migration with `php artisan migrate`.
2. Run `php artisan storage:link` so uploaded room and website images can be displayed.
3. Sign in as an Owner or Hotel Manager and open **Website Content**. Select the Lodge whose rooms should appear on the website. If there are multiple lodges and no property is selected, public room data stays empty until one is configured.
4. In **Rooms**, publish accurate descriptions, bed type, capacity, tax and breakfast details, and approved room photos. Existing room rates and features supply public rates and amenities.
5. In **Website Content**, add only approved services, offers, and gallery photos. Configure the contact phone, address, map link, check-in/out times, guest policies, and currency. Contact email defaults to `info@hardrockexecutivelodge.co.tz`; currency defaults to `TZS`.
6. Booking, payment, cancellation, and privacy policies show a draft notice until an authorized staff member enters and approves them.
7. Open **Website Content → Website settings** and configure SMTP host, port, encryption, username, password, and sender address for self-service booking emails. The SMTP password is encrypted in the database using `APP_KEY`; set a stable application key before saving credentials and keep it backed up. Booking requests use these settings to email the customer and lodge. When portal SMTP is disabled, Laravel uses the mailer configured in `.env` instead. Enquiries are saved for staff review even if mail delivery fails.
8. Set `APP_URL` to the public site URL so generated sitemap links use the correct host. `WEBSITE_LODGE_ID` can also select the public Lodge through environment configuration.

Public staff registration is disabled; authorized staff accounts continue to be managed through the existing user administration area. Guest accounts and online payments are not enabled.
