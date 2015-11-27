# Symfony2 Logstash handler for Monolog

Use in Symfony2 project with an ELK architecture, ie : https://github.com/pilebones/elk-stack-docker

## Requirements

- Symfony2
- Monolog
- ELK (Logstash, Elasticsearch, Kibana)

## Introduction

From Symfony2 application, this handler permit to send the Symfony2 logs stream to an instance of Logstash server.

This Handler aggregate all lines from a single Symfony2 request to push to Logstash inside only-one entry like UTF-8 JSON format.

__/!\ Warning :__ Logstash must be configure with "input-tcp" with "input-codec-json" !

You can enrich log stream with some custom attributes (see bellow).

## Installation

Edit composer.json :
```yml
    [...]
    "require" : {
        [...]
        "pilebones/logstash-bundle" : "dev-master"
    },
    "repositories" : [{
        "type" : "vcs",
        "url" : "https://github.com/pilebones/logstashMonologHandlerBundle.git"
    }],
    [...]
```

Run composer.phar to install the bundle :
```bash
php composer.phar update "pilebones/logstash-bundle"
```

## Project settings

In app/AppKernel : 

```php
new Pilebones\LogstashBundle\PilebonesLogstashBundle(),
```

In app/config/config.yml : 

```yml
imports:
    # [...]
    - { resource: pilebones_logstash.yml }

monolog:
    handlers:
        main:
            type:         fingers_crossed
            action_level: error
            handler:      nested
        nested:
            type:  stream
            path:  "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
            handler: logstash
        console:
            type:  console
        logstash:
            type: service
            id: pilebones_logstash.monolog.logstash_handler
```

In app/config/pilebones_logstash.yml : 

```yml
pilebones_logstash:
	# Allowed format : http://php.net/manual/fr/transports.php
    logstash_address: tcp://localhost:25826
    
    # Optionnal parameters
    custom_log_attributes:
        corporate: MyOfficeName
        project: POC Sf2 vs Logstash 
        developper_name: pilebones
```
