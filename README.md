<div align="center">

# Hyperf WHERE HAS IN

<p>
    <a href="https://github.com/sllhSmile/wherehasin/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-7389D8.svg?style=flat" ></a>
     <a href="https://github.com/sllhSmile/wherehasin/actions">
        <img src="https://github.com/sllhSmile/wherehasin/workflows/Phpunit/badge.svg" alt="Build Status">
    </a>
    <a href="https://github.com/sllhSmile/wherehasin/releases"><img src="https://img.shields.io/github/v/release/sllhSmile/wherehasin.svg?color=4099DE" /></a> 
    <a href="https://packagist.org/packages/sllhsmile/wherehasin"><img src="https://img.shields.io/packagist/dt/sllhSmile/wherehasin.svg?color=" /></a> 
    <a><img src="https://img.shields.io/badge/php-7+-59a9f8.svg?style=flat" /></a> 
</p>

</div>

`Hyperf wherehasin`是一个可以提升`Hyperf ORM`关联关系查询性能的扩展包，可以替代`Hyperf ORM`中的`whereHas`以及`whereHasMorphIn`查询方法。


## 环境

- PHP >= 7
- Hyperf >= 3.0


## 安装

```bash
  composer require sllhsmile/wherehasin
```

### 简介

`Hyperf`的关联关系查询`whereHas`在日常开发中给我们带来了极大的便利，但是在**主表**数据量比较多的时候会有比较严重的性能问题，主要是因为`whereHas`用了`where exists (select * ...)`这种方式去查询关联数据。


通过这个扩展包提供的`whereHasIn`方法，可以把语句转化为`where id in (select xxx.id ...)`的形式，从而提高查询性能，下面我们来做一个简单的对比：


> 当主表数据量较多的情况下，`where id in`会有明显的性能提升；当主表数据量较少的时候，两者性能相差无几。


主表`test_users`写入`130002`条数据，关联表`test_user_profiles`写入`1002`条数据，查询代码如下

```php
<?php
/**
 * SQL:
 * 
 * select * from `test_users` where exists
 *   (
 *     select * from `test_user_profiles` 
 *     where `test_users`.`id` = `test_user_profiles`.`user_id`
 *  ) 
 * limit 10
 */
$users1 = User::whereHas('profile')->limit(10)->get();

/**
 * SQL:
 * 
 * select * from `test_users` where `test_users`.`id` in 
 *   (
 *     select `test_user_profiles`.`user_id` from `test_user_profiles` 
 *     where `test_users`.`id` = `test_user_profiles`.`user_id`
 *   ) 
 * limit 10
 */
$users1 = User::whereHasIn('profile')->limit(10)->get();
```

最终耗时如下，可以看出性能相差还是不小的，如果数据量更多一些，这个差距还会更大

```bash
whereHas   0.50499701499939 秒
whereHasIn 0.027166843414307 秒
```


### 使用

#### whereHasIn

此方法已支持`Hyperf ORM`中的所有关联关系，可以替代`whereHas`

```php
User::whereHasIn('profile')->get();

User::whereHasIn('profile', function ($q) {
    $q->where('id', '>', 10);
})->get();
```

orWhereHasIn

```php
User::where('name', 'like', '%Hyperf%')->orWhereHasIn('profile')->get();
```

多级关联关系
```php
User::whereHasIn('painters.paintings', function ($q) {
    $q->whereIn('id', [600, 601]);
})->orderBy('id')->get()->toArray();

```

需要注意的是，如果是`BelongsTo`类型的关联关系，使用`whereHasIn`时使用的不是主键，而是外键

```php
<?php

/**
 * 这里用的是"user_id in"，而不是"id in"
 * 
 * select * from `test_user_profiles` where `test_user_profiles`.`user_id` in 
 *   (
 *     select `test_users`.`id` from `test_users` where `test_user_profiles`.`user_id` = `test_users`.`id`
 *   )
 */
$profiles = Profile::whereHasIn('user')->get();
```

#### whereHasMorphIn

此方法已支持`Hyperf ORM`中的所有关联关系，可以替代`whereHasMorph`

```php
Image::whereHasMorphIn('imageable', Post::class, function ($q) {
    $q->where('id', '>', 10);
})->get();
```

>特别鸣谢
>https://github.com/jqhph/laravel-wherehasin 


## License
[The MIT License (MIT)](LICENSE).
