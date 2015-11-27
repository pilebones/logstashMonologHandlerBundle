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

## Getting started

### From Symfony2 project

In composer.json :
```json
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
In app/AppKernel : 

```php
new Pilebones\LogstashBundle\PilebonesLogstashBundle(),
```

### Bundle Settings

In app/config/config.yml : 

```yml
imports:
    [...]
    - { resource: pilebones_logstash.yml }
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
