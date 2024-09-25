<?php

namespace zcrmsdk\crm\crud;

class ZCRMProfileSection
{
    /**
     * name of the profile section.
     */
    private string $name;

    /**
     * categories of profile.
     */
    private array $categories = [];

    /**
     * constructor to assign the name to the profile section.
     *
     * @param string $name name of the section
     */
    private function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * method to get the instance of the profile section.
     *
     * @param string $name name of the profile section
     *
     * @return ZCRMProfileSection instance of the ZCRMProfileSection
     */
    public static function getInstance(string $name): ZCRMProfileSection
    {
        return new ZCRMProfileSection($name);
    }

    /**
     * method to get the name of profile section.
     *
     * @return string the name of the profile section
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * method to set the name of profile section.
     *
     * @param array $name the name of the profile section
     */
    public function setName(null|string $name): void
    {
        $this->name = $name;
    }

    /**
     * method to get the categories to the profile section.
     *
     * @return array<ZCRMProfileCategory>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * method to add the category to the profile section.
     *
     * @param array $categoryIns ZCRMProfileCategory class instance
     */
    public function addCategory(null|ZCRMProfileCategory $categoryIns): void
    {
        $this->categories[] = $categoryIns;
    }
}
