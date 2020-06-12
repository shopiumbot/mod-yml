mod-yml
===========
Module for yml PIXELION CMS

[![Latest Stable Version](https://poser.pugx.org/shopium/mod-yml/v/stable)](https://packagist.org/packages/shopium/mod-yml)
[![Total Downloads](https://poser.pugx.org/shopium/mod-yml/downloads)](https://packagist.org/packages/shopium/mod-yml)
[![Monthly Downloads](https://poser.pugx.org/shopium/mod-yml/d/monthly)](https://packagist.org/packages/shopium/mod-yml)
[![Daily Downloads](https://poser.pugx.org/shopium/mod-yml/d/daily)](https://packagist.org/packages/shopium/mod-yml)
[![Latest Unstable Version](https://poser.pugx.org/shopium/mod-yml/v/unstable)](https://packagist.org/packages/shopium/mod-yml)
[![License](https://poser.pugx.org/shopium/mod-yml/license)](https://packagist.org/packages/shopium/mod-yml)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer require --prefer-dist shopium/mod-yml "*"
```

or add

```
"shopium/mod-yml": "*"
```

to the require section of your `composer.json` file.

Add to web config.
```
'modules' => [
    'yml' => ['class' => 'shopium\mod\yml\Module'],
],
```

