<?php

namespace KikCMS\Models;

use DateTime;
use KikCMS\Classes\Model\Model;

class FinderFile extends Model
{
    const TABLE = 'finder_file';

    const FIELD_ID        = 'id';
    const FIELD_FOLDER_ID = 'folder_id';
    const FIELD_IS_FOLDER = 'is_folder';
    const FIELD_NAME      = 'name';

    const IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    /**
     * @inheritdoc
     * @return FinderFile
     */
    public static function getById($id)
    {
        /** @var FinderFile $finderFile */
        $finderFile = parent::getById($id);

        return $finderFile;
    }

    /**
     * @inheritdoc
     *
     * @return FinderFile[]
     */
    public static function getByIdList(array $ids)
    {
        /** @var FinderFile[] $finderFiles */
        $finderFiles = parent::getByIdList($ids);

        return $finderFiles;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return new DateTime($this->created);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return int
     */
    public function getFolderId(): int
    {
        return (int) $this->folder_id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getThumbNail()
    {
        return $this->fileStorage;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimetype;
    }

    /**
     * @return bool
     */
    public function isFolder(): bool
    {
        return (bool) $this->is_folder;
    }

    /**
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array($this->getMimeType(), self::IMAGE_TYPES);
    }
}