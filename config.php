<?php

  /**
   * Конфиг нужно помещать в .gitignore,
   * лучше работать с .env, чем с файлом PHP
   */

  declare(strict_types=1);

  const DB_DSN = 'pgsql:dbname=publications;host=localhost;port=5433';

  const ADMIN_LOGIN = 'pub_admin';
  const ADMIN_PASSWORD = 'adm4321';

  const NORMAL_USER_LOGIN = 'pub_user';
  const NORMAL_USER_PASSWORD = 'user1234';

  const POSTS_URL = 'https://jsonplaceholder.typicode.com/posts';
  const COMMENTS_URL = 'https://jsonplaceholder.typicode.com/comments';
