# Nette CMS

This is a simple integration of cms into [Nette Framework](http://nette.org/)

## Instalation

The best way to install salamek/nette-cms is using  [Composer](http://getcomposer.org/):


```sh
$ composer require salamek/nette-cms:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	cms: Salamek\Cms\DI\CmsExtension

cms:
    tempPath: %tmpDir%/cms
    presenterNamespace: FrontModule
```
