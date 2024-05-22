<?php

namespace Krombox\DownloadableLinksSync\Model;

use Krombox\DownloadableLinksSync\Api\MessageInterface;

class Message implements MessageInterface
{
    protected string $action;
    protected int $linkId;
    /** @var array<string> $ids */
    protected array $ids;

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function getLinkId()
    {
        return $this->linkId;
    }

    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;
        return $this;
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function setIds($ids)
    {
        $this->ids = $ids;
        return $this;
    }
}
