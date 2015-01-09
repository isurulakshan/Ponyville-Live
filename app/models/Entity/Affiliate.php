<?php
namespace Entity;

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @Table(name="affiliates")
 * @Entity
 */
class Affiliate extends \DF\Doctrine\Entity
{
    public function __construct()
    {
        $this->timestamp = time();
        $this->is_approved = true;
    }

    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /** @Column(name="name", type="string", length=255) */
    protected $name;

    /** @Column(name="description", type="text", nullable=true) */
    protected $description;

    /** @Column(name="image_url", type="string", length=255) */
    protected $image_url;

    public function setImageUrl($new_url)
    {
        if ($new_url)
        {
            if ($this->image_url && $this->image_url != $new_url)
                @unlink(DF_UPLOAD_FOLDER.DIRECTORY_SEPARATOR.$this->image_url);

            $this->image_url = $new_url;
        }
    }

    /** @Column(name="web_url", type="string", length=255) */
    protected $web_url;

    /** @Column(name="timestamp", type="integer") */
    protected $timestamp;

    /** @Column(name="is_approved", type="boolean") */
    protected $is_approved;

    /**
     * Static Functions
     */

    public static function fetch($only_approved = true)
    {
        $records = self::fetchArray();

        if ($only_approved)
            $records = array_filter($records, function($record) { return $record['is_approved']; });

        shuffle($records);

        return $records;
    }
}