# Puzzle News Bundle

Project based on Symfony project for managing news accounts and news security.

## **Install bundle**

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```yaml
composer require webundle/puzzle-news-bundle
```

## **Step 1: Enable bundle**
Enable admin bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Puzzle\NewsBundle\NewsBundle(),
        );

        // ...
    }

    // ...
}
```

## **Step 2: Configure bundle security**
Configure security by adding it in the `app/config/security.yml` file of your project:

```yaml
security:
   	...
    role_hierarchy:
        ...
        # News
        ROLE_NEWS: ROLE_ADMIN
        ROLE_SUPER_ADMIN: [..,ROLE_NEWS]
        
	...
    access_control:
        ...
        # News
        - {path: ^%admin_prefix%news, host: "%admin_host%", roles: ROLE_NEWS }

```

## **Step 3: Enable bundle routing**

Register default routes by adding it in the `app/config/routing.yml` file of your project:

```yaml
....
news:
    resource: "@NewsBundle/Resources/config/routing.yml"
    prefix:   /
```
See all news routes by typing: `php bin/console debug:router | grep news`

## **Step 4: Configure bundle**

Configure admin bundle by adding it in the `app/config/config.yml` file of your project:

```yaml
admin:
    ...
    modules_available: '..,news'
    navigation:
        nodes:
            ...
            # News
            news:
                label: 'news.title'
                description: 'news.description'
                translation_domain: 'news'
                attr:
                    class: 'fa fa-newspaper-o'
                parent: ~
                user_roles: ['ROLE_NEWS']
            news_post:
                label: 'news.post.navigation'
                description: 'news.post.description'
                translation_domain: 'news'
                path: 'puzzle_admin_news_post_list'
                sub_paths: ['puzzle_admin_news_post_create', 'puzzle_admin_news_post_update', 'puzzle_admin_news_post_update_gallery',  'puzzle_admin_news_post_show', 'puzzle_admin_news_comment_list']
                parent: news
                user_roles: ['ROLE_NEWS']
            news_category:
                label: 'news.category.navigation'
                description: 'news.category.description'
                translation_domain: 'news'
                path: 'puzzle_admin_news_category_list'
                sub_paths: ['puzzle_admin_news_category_create', 'puzzle_admin_news_category_update', 'puzzle_admin_news_category_show']
                parent: news
                user_roles: ['ROLE_NEWS']
```
