<?php

namespace Krombox\DownloadableLinksSync\Api;

interface MessageInterface
{
    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $action
     *
     * @return self
     */
    public function setAction($action);

    /**
     * @return int
     */
    public function getLinkId();

    /**
     * @param int $linkId
     *
     * @return self
     */
    public function setLinkId($linkId);

    /**
     * @return string[] $ids
     */
    public function getIds();

    /**
     * @param string[] $ids
     *
     * @return self
     */
    public function setIds($ids);
}
