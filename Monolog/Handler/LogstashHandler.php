<?php

namespace Pilebones\LogstashBundle\Monolog\Handler;

use Monolog\Handler\SocketHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LogstashHandler extends SocketHandler
{
    /** @var  Request $request */
    protected $request;

    /** @var  array $customAttributes */
    protected $customAttributes = array();

    /**
     * LogstashHandler constructor.
     *
     * @param RequestStack $requestStack
     * @param bool|int $connectionString
     * @param bool $level
     * @param $bubble
     */
    public function __construct(RequestStack $requestStack, $connectionString, $level, $bubble, $customAttributes = array()){
        parent::__construct($connectionString, $level, $bubble);

        if ($requestStack) {
            $this->setRequest($requestStack->getCurrentRequest());
        }

        if (0 < count($customAttributes)) {
            $this->setCustomAttributes($customAttributes);
        }
    }

    /**
     * @param array $record
     */
    /*public function handle(array $record)
    {
        throw new \BadMethodCallException("This handler only handles batch records");
    }*/

    /**
     * @param $record
     * @return string
     */
    protected function generateDataStream($record)
    {
        $formatedRecord = (string) json_encode($record);
        // Don't miss UTF-8 encode + CR char ("\n") to work with input-codec-json settings for Logstash
        $buffer = utf8_encode($formatedRecord)."\n";
        return $buffer;
    }

    /**
     * @param array $records
     * @return bool
     */
    public function handleBatch(array $records)
    {
        $final = array_merge($this->getCustomAttributes(),[
            'datetime' => date(\DateTime::ISO8601),
            'timestamp' => time(),
            'client_ip' => null,
            '_type' => $this->getLevel(),
            'level' => $this->getLevel()
        ]);

        // Usefull for Logstash GeoIP
        if ($this->getRequest()) {
            $final['client_ip'] = $this->getRequest()->server->get('REMOTE_ADDR');
        }

        // Aggregate all lines from a single Symfony request to push to Logstash inside only-one entry
        foreach ($records as $record) {
            if (!$this->isHandling($record)) {
                return false;
            }
            $record = $this->processRecord($record);
            if (empty($final['message'])) {
                $final['message'] = $record['message'];
            }
            $final['records'][] = $record;
        }
        $this->write($final);
    }

    /**
     * @param array $rawrecord
     * @return array
     */
    protected function processRecord(array $rawrecord)
    {
        $record = parent::processRecord($rawrecord);
        $record['datetime'] = $record['datetime']->format(\DateTime::ISO8601);

        if (empty($record['extra'])) {
            unset($record['extra']);
        }
        if (isset($record['context'])) {
            $context = $record['context'];
            if (isset($context['exception'])) {
                $record['exception'] = $this->parseException($context['exception']);
                unset($record['context']['exception']);
            }
        }
        if (empty($record['context'])) {
            unset($record['context']);
        }
        if (!empty($record['level_name'])) {
            $record['level'] = $record['level_name'];
            unset($record['level_name']);
        }
        return $record;
    }

    /**
     * @param \Exception|null $e
     * @return array
     */
    protected function parseException(\Exception $e = null)
    {
        $arrayException = [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
        ];

        if ($e->getPrevious() && $e->getPrevious()->getMessage()) {
            $arrayException['previous'] = $this->parseException($e->getPrevious());
        }
        if ($e->getTraceAsString()) {
            $arrayException['trace'] = $e->getTraceAsString();
        }
        return $arrayException;
    }

    /**
     * @return RequestStack
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomAttributes()
    {
        return $this->customAttributes;
    }

    /**
     * @param $customAttributes
     * @return $this
     */
    public function setCustomAttributes(array $customAttributes)
    {
        $this->customAttributes = $customAttributes;
        return $this;
    }
}
