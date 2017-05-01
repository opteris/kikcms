<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Translator;
use KikCMS\Forms\LanguageForm;
use KikCMS\Models\Language;

/**
 * @property Translator $translator
 */
class Languages extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return LanguageForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return [
            $this->translator->tlb('dataTables.language.singular'),
            $this->translator->tlb('dataTables.language.plural')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Language::class;
    }

    /**
     * @inheritdoc
     */
    protected function getTableFieldMap(): array
    {
        return [
            'id'   => $this->translator->tlb('id'),
            'code' => $this->translator->tlb('code'),
            'name' => $this->translator->tlb('name'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // nothing here...
    }
}