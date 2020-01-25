<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\UrlInterface;

/**
 * SearchResult
 *
 * @ORM\Entity(repositoryClass="Zikula\SearchModule\Entity\Repository\SearchResultRepository")
 * @ORM\Table(name="search_result")
 */
class SearchResultEntity
{
    /**
     * ID of the search
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * title of the search
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Assert\Length(min="0", max="255", allowEmptyString="false")
     * @var string
     */
    private $title;

    /**
     * the matching search text
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     * @var string
     */
    private $text;

    /**
     * the module providing the search hit
     *
     * @ORM\Column(name="module", type="string", length=100, nullable=true)
     * @Assert\Length(min="0", max="100", allowEmptyString="true")
     * @var string
     */
    private $module;

    /**
     * additional information about this search result
     *
     * @ORM\Column(name="extra", type="array")
     * @var array
     */
    private $extra = [];

    /**
     * creation timestamp of this search hit
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     * @var DateTime
     */
    private $created;

    /**
     * Last found timestamp of this search hit
     *
     * @ORM\Column(name="found", type="datetime", nullable=true)
     * @var DateTime
     */
    private $found;

    /**
     * Session id associated
     *
     * @ORM\Column(name="sesid", type="string", length=50, nullable=true)
     * @Assert\Length(min="0", max="50", allowEmptyString="true")
     * @var string
     */
    private $sesid;

    /**
     * Url for found item
     *
     * @ORM\Column(type="object", nullable=true)
     * @var UrlInterface
     */
    private $url;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @param string|array $extra
     */
    public function setExtra($extra): self
    {
        if (!is_array($extra)) {
            $this->extra = [$extra];
        } else {
            $this->extra = $extra;
        }

        return $this;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setFound(DateTime $found): self
    {
        $this->found = $found;

        return $this;
    }

    public function getFound(): DateTime
    {
        return $this->found;
    }

    public function setSesid(string $sesid): self
    {
        $this->sesid = $sesid;

        return $this;
    }

    public function getSesid(): string
    {
        return $this->sesid;
    }

    public function setUrl(UrlInterface $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): UrlInterface
    {
        return $this->url;
    }

    public function merge(array $result = []): void
    {
        $this->title = $result['title'] ?? 'unknown';
        $this->text = $result['text'] ?? null;
        $this->extra = $result['extra'] ?? null;
        $this->module = $result['module'] ?? null;
        $this->created = (isset($result['created']) && $result['created'] instanceof DateTime) ? $result['created'] : new DateTime('now', new DateTimeZone('UTC'));
        $this->sesid = $result['sesid'] ?? null;
        $this->url = (isset($result['url']) && $result['url'] instanceof UrlInterface) ? $result['url'] : null;
    }
}
