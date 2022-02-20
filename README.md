## installation :

after install the source code open Terminal to use these command line:

- Run `composer install` ( may need to use "composer update").
- Rename or copy `.env.example` file to `.env` and write required database information.
- create new `database`.
- Use your configuration in `.env`
- Run `php artisan key:generate` command.
- Run `php artisan migrate --seed` command.
- Run `php artisan passport:install` command.
- Run `php artisan serve` command.
- Run `php artisan queue:listen` command.


### Features
- Transfer flow between two or more users.
- Users can cancel a scheduled transaction

## Packages Used
### [Laravel Passport](https://laravel.com/docs/8.x/passport).

### [Laravel Permission](https://spatie.be/docs/laravel-permission/v4/prerequisites)

### [Laravel Auditing](http://www.laravel-auditing.com/)

## License

The [Laravel framework](https://laravel.com) is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
