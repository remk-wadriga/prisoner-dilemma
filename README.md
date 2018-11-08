# "The prisoner dilemma" of game theory checker (BETA)

PHP application based on <a href="https://github.com/symfony/symfony">Symfony 4</a> and <a href="https://github.com/vuejs/vue">Vue</a> frameworks.
Created for check which behavior strategy is more effective.
Now it can make strategies and games but in next releases we will add a statistics.

## Installation

Install the latest version of symfony and it's components:
```bash
$ composer install
```

Install vue and other frontend components:
```bash
$ npm i
```

Create local config file /public/js/src/config.js (based on /public/js/src/config.js.sample) and change Config.api.baseUrl value to your local api url:
```js
const Config = {
    api: {
        baseUrl: "http://your-local-api.addr"
    }
};
export default Config;
```

Create database and paste connection config into file .env (based on .env.dist):
```bash
# Configure your db driver and server_version in config/packages/doctrine.yaml
DATABASE_URL=mysql://root:@127.0.0.1:3306/your_db_name
```

If you use not MySQL DB, you should note this in db connection config line, then note your DB driver in file /config/packages/doctrine.yaml, then delete all migrations (/src/Migrations) and then generate new ones:
```bash
$ ./bin/console make:migration
```

Now you can make migrations to create needed DB structure:
```bash
$ ./bin/console doctrine:migrations:migrate
```

Congratulations! You have just installed "The prisoner dilemma" application.


## Basic Usage

Generate fake data:
```bash
$ ./bin/console doctrine:fixtures:load
```
Now you have few users with different roles (admin@gmail.com, owner@gmail.com and user@gmail.com with password "test"), Also you can register your own user (but user user@gmail.com is required for tests) and some strategies.

Run local npm dev-server:
```bash
$ cd public/js
$ npm run dev
```

And have fun!