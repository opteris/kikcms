<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use Exception;
use InvalidArgumentException;
use KikCMS\Classes\DbService;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Services\TranslationService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Handles the storage of a single field
 *
 * @property DbService $dbService
 * @property TranslationService $translationService
 */
class FieldStorageService extends Injectable
{
    /**
     * @param Field $field
     * @param int $relationId
     * @return int
     */
    public function getTranslationKeyId(Field $field, int $relationId = null): int
    {
        if( ! $relationId){
            return $this->translationService->createNewTranslationKeyId();
        }

        $query = (new Builder())
            ->from($field->getStorage()->getTableModel())
            ->columns($field->getColumn())
            ->where('id = :id:', ['id' => $relationId]);

        if ($translationKeyId = $this->dbService->getValue($query)) {
            return $translationKeyId;
        }

        return $this->translationService->createNewTranslationKeyId();
    }

    /**
     * @param Field $field
     * @param $value
     * @param $editId
     * @param string|null $langCode
     * @return bool
     * @throws Exception
     */
    public function store(Field $field, $value, $editId, string $langCode = null): bool
    {
        $storage = $field->getStorage();

        switch (true) {
            case $storage instanceof OneToOne:
                return $this->storeOneToOne($field, $value, $editId, $langCode);
            break;

            case $storage instanceof OneToMany:
                return $this->storeOneToMany($field, $editId);
            break;

            case $storage instanceof ManyToMany:
                return $this->storeManyToMany($field, $value, $editId, $langCode);
            break;

            default:
                throw new Exception('Not implemented yet');
            break;
        }
    }

    /**
     * @param Field $field
     * @param $value
     * @param $editId
     * @param string|null $langCode
     *
     * @return bool|mixed
     */
    public function storeOneToOne(Field $field, $value, $editId, string $langCode = null)
    {
        $storage = $field->getStorage();
        $value   = $this->dbService->toStorage($value);

        $set   = [$field->getColumn() => $value];
        $where = $storage->getDefaultValues() + [$storage->getRelatedField() => $editId];

        if ($storage->isAddLanguageCode()) {
            $where[$storage->getLanguageCodeField()] = $langCode;
        }

        if ($this->relationRowExists($field, $editId, $langCode)) {
            return $this->dbService->update($storage->getTableModel(), $set, $where);
        }

        return $this->dbService->insert($storage->getTableModel(), $set + $where);
    }

    /**
     * @param Field|DataTableField $field
     * @param $editId
     * @return bool
     */
    public function storeOneToMany(Field $field, $editId): bool
    {
        $dataTable = $field->getDataTable();

        $keysToUpdate = $dataTable->getCachedNewIds();
        $relatedField = $dataTable->getParentRelationKey();
        $model        = $dataTable->getModel();

        foreach ($keysToUpdate as $newId) {
            $success = $this->dbService->update($model, [$relatedField => $editId], ['id' => $newId, $relatedField => 0]);

            if( ! $success){
                return false;
            }
        }

        return true;
    }

    /**
     * @param Field $field
     * @param $value
     * @param $editId
     * @param string|null $langCode
     *
     * @return bool
     */
    public function storeManyToMany(Field $field, $value, $editId, string $langCode = null): bool
    {
        if( ! $value){
            $value = [];
        }

        if ( ! is_array($value)) {
            throw new InvalidArgumentException(static::class . ' can only store array values');
        }

        $key     = $field->getKey();
        $storage = $field->getStorage();

        $table        = $storage->getTableModel();
        $relatedField = $storage->getRelatedField();

        $where = [$relatedField => $editId] + $storage->getDefaultValues();

        if ($storage->isAddLanguageCode()) {
            $where[$storage->getLanguageCodeField()] = $langCode;
        }

        $this->dbService->delete($table, $where);

        foreach ($value as $id) {
            $insert       = $where;
            $insert[$key] = $id;

            $this->dbService->insert($table, $insert);
        }

        return true;
    }

    /**
     * @param Field $field
     * @param $id
     * @param string|null $langCode
     *
     * @return array
     */
    public function retrieveManyToMany(Field $field, $id, string $langCode = null)
    {
        $storage = $field->getStorage();

        $query = (new Builder())
            ->columns($field->getKey())
            ->addFrom($storage->getTableModel())
            ->andWhere($storage->getRelatedField() . ' = ' . $id);

        if ($storage->isAddLanguageCode()) {
            $query->andWhere($storage->getLanguageCodeField() . ' = :langCode:', [
                'langCode' => $langCode
            ]);
        }

        return $this->dbService->getValues($query);
    }

    /**
     * @param Field $field
     * @param $id
     * @param string|null $langCode
     *
     * @return mixed
     */
    public function retrieveOneToOne(Field $field, $id, string $langCode = null)
    {
        $query = $this->getRelationQuery($field, $id, $langCode)->columns($field->getColumn());
        return $this->dbService->getValue($query);
    }

    /**
     * todo: implement this
     * @param Field $field
     * @param $id
     * @param string|null $langCode
     *
     * @return mixed
     */
    public function retrieveOneToOneRef(Field $field, $id, string $langCode = null)
    {
        return '';
    }

    /**
     * @param Field $field
     * @param $id
     * @param string|null $langCode
     *
     * @return null|string
     */
    public function retrieveTranslation(Field $field, $id, string $langCode = null)
    {
        $langCode = $field->getStorage()->getLanguageCode() ?: $langCode;
        $translationKeyId = $this->getTranslationKeyId($field, $id);

        return $this->translationService->getTranslationValue($translationKeyId, $langCode);
    }

    /**
     * @param Field $field
     * @param $relationId
     * @param string $langCode
     *
     * @return Builder
     */
    private function getRelationQuery(Field $field, $relationId, string $langCode = null)
    {
        /** @var FieldStorage $storage */
        $storage = $field->getStorage();

        $relationQuery = (new Builder())
            ->addFrom($storage->getTableModel())
            ->where($storage->getRelatedField() . ' = ' . $relationId);

        foreach ($storage->getDefaultValues() as $field => $value) {
            $relationQuery->andWhere($field . ' = ' . $this->db->escapeString($value));
        }

        if ($storage->isAddLanguageCode()) {
            $relationQuery->andWhere($storage->getLanguageCodeField() . ' = ' . $this->db->escapeString($langCode));
        }

        return $relationQuery;
    }

    /**
     * @param Field $field
     * @param $relationId
     * @param string $langCode
     *
     * @return bool
     */
    private function relationRowExists(Field $field, $relationId, $langCode = null): bool
    {
        return $this->dbService->getExists($this->getRelationQuery($field, $relationId, $langCode));
    }
}