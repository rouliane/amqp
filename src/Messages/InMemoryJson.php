<?php

namespace Puzzle\AMQP\Messages;

use Puzzle\AMQP\ReadableMessage;
use Puzzle\AMQP\WritableMessage;
use Puzzle\AMQP\Collections\MessageHookCollection;

class InMemoryJson extends Json implements ReadableMessage, WritableMessage
{
    public function getRawBody()
    {
        return $this->getFormattedBody();
    }

    public function getDecodedBody()
    {
        return $this->body;
    }

    public function getAttributes()
    {
        return $this->packAttributes();
    }

    public function isLastRetry($retryOccurence = \Puzzle\AMQP\Consumers\Retry::DEFAULT_RETRY_OCCURENCE)
    {
        $retryHeader = $this->getHeader(\Puzzle\AMQP\Consumers\Retry::DEFAULT_RETRY_HEADER);

        return (!empty($retryHeader) && (int) $retryHeader === $retryOccurence);
    }

    public function applyHooks(MessageHookCollection $messageHookCollection)
    {
        $body = $this->body;

        foreach($messageHookCollection as $hook)
        {
            $body = $hook->process($body);
        }

        $this->body = $body;
    }

    public function getRoutingKeyFromHeader()
    {
        $headers = $this->getHeaders();

        if(! array_key_exists('routing_key', $headers))
        {
            return null;
        }

        return $headers['routing_key'];
    }
}
