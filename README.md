## Requirements

PHP 8.3 or higher
[Composer](https://getcomposer.org/)
[Node version 18 or higher](https://nodejs.org/en/download)

PHP Extension
sodium library


## PHP Packages Used

### Laravel Permission

[laravel-permission](https://spatie.be/docs/laravel-permission/v6/introduction)

`composer require spatie/laravel-permission`

### Laravel CSP

[laravel-csp](https://github.com/spatie/laravel-csp)

`composer require spatie/laravel-csp`

### Laravel Pulse

[Laravel Pulse](https://pulse.laravel.com/)

`composer require laravel/pulse`

### Laravel Modules

[Laravel Modules](https://nwidart.com/laravel-modules/v6/introduction)

`composer require nwidart/laravel-modules`

### Laravel Ciphersweet

[Laravel Ciphersweet](https://github.com/spatie/laravel-ciphersweet)

`composer require spatie/laravel-ciphersweet`

### Laravel Activitylog

[Laravel Activitylog](https://spatie.be/docs/laravel-activitylog/v4/introduction)

`composer require spatie/laravel-activitylog`

### Laravel Eloquent Filtering

[Laravel Eloquent Filtering](https://docs.eloquentfiltering.com/v2/introduction/eloquent-filtering)

`composer require indexzer0/eloquent-filtering`


## Javascript Packages

### Javascript Obfuscator

[vite-plugin-javascript-obfuscator](https://github.com/elmeet/vite-plugin-javascript-obfuscator?tab=readme-ov-file)

`npm install --save-dev vite-plugin-javascript-obfuscator`


NEW

`npm install --save-dev vite-plugin-obfuscator`



### Shadcn React UI

[Shadcn React UI](https://ui.shadcn.com/)

```
npx shadcn@latest add table
npm install @tanstack/react-table
```

`npx shadcn@latest add form`

`npx shadcn@latest add toast`

`npx shadcn@latest add popover`

`npx shadcn@latest add command`

`npm i @radix-ui/react-icons`

`npm i tailwind-merge`

`npm i tailwindcss-animate`

`npm i clsx`

`npm install zod`


## Referrences

[Frontend UI (shadcn)](https://ui.shadcn.com)

[Radix Icons](https://www.radix-ui.com/icons)

[Sample Laravel Implementation](https://github.com/raprmdn/laravel-inertia-datatable)


## Deployment

```
git clone git@github.com:doh-ihomis/laravel-react-starterkit.git

cd laravel-react-starterkit

composer install

cp .env.example .env

# Update database credentials

php artisan key:generate

php artisan ciphersweet:generate-key

php artisan migrate

php artisan module:seed IAM

npm install

npm run dev
```


```
email: admin@example.com
password: 4dm1n1str4t0r
```