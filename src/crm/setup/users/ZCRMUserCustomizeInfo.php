<?php

namespace zcrmsdk\crm\setup\users;

class ZCRMUserCustomizeInfo
{
    /**
     * notes description.
     *
     * @var string
     */
    private $notesDesc;

    /**
     * show right panel.
     *
     * @var bool
     */
    private $isToShowRightPanel;

    /**
     * business card view.
     *
     * @var bool
     */
    private $isBcView;

    /**
     * show home.
     *
     * @var bool
     */
    private $isToShowHome;

    /**
     * shown detail view.
     *
     * @var bool
     */
    private $isToShowDetailView;

    /**
     * shown to right panel.
     *
     * @var string
     */
    private $unpinRecentItem;

    private function __construct()
    {
    }

    /**
     * method to get user customize information.
     *
     * @return ZCRMUserCustomizeInfo instance of the ZCRMUserCustomizeInfo
     */
    public static function getInstance()
    {
        return new ZCRMUserCustomizeInfo();
    }

    /**
     * method to get the notes description.
     *
     * @return string the notes description
     */
    public function getNotesDesc()
    {
        return $this->notesDesc;
    }

    /**
     * method to set the notes description.
     *
     * @param string $notesDesc the notes desc
     */
    public function setNotesDesc($notesDesc)
    {
        $this->notesDesc = $notesDesc;
    }

    /**
     * method to check whether right panel is shown.
     *
     * @return bool true if the right panel is shown else false
     */
    public function isToShowRightPanel()
    {
        return $this->isToShowRightPanel;
    }

    /**
     * method to show right panel.
     *
     * @param bool $isToShowRightPanel true to show right panel otherwise false
     */
    public function setIsToShowRightPanel($isToShowRightPanel)
    {
        $this->isToShowRightPanel = $isToShowRightPanel;
    }

    /**
     * method to check whether business card view is shown.
     *
     * @return bool true if the business card view is shown else false
     */
    public function isBcView()
    {
        return $this->isBcView;
    }

    /**
     * method to show bcview.
     *
     * @param bool $isBcView true to show business card view otherwise false
     */
    public function setBcView($isBcView)
    {
        $this->isBcView = $isBcView;
    }

    /**
     * method to check whether home is shown.
     *
     * @return bool true if the home is shown else false
     */
    public function isToShowHome()
    {
        return $this->isToShowHome;
    }

    /**
     * method to show home.
     *
     * @param bool $isToShowHome true to show home otherwise false
     */
    public function setIsToShowHome($isToShowHome)
    {
        $this->isToShowHome = $isToShowHome;
    }

    /**
     * method to check whether detail view is shown.
     *
     * @return bool true if the detailed view is shown else false
     */
    public function isToShowDetailView()
    {
        return $this->isToShowDetailView;
    }

    /**
     * method to show detail view.
     *
     * @param bool $isToShowDetailView true to show detail view otherwise false
     */
    public function setIsToShowDetailView($isToShowDetailView)
    {
        $this->isToShowDetailView = $isToShowDetailView;
    }

    /**
     * method get the recent unpinned item.
     *
     * @return string the recent unpinned item
     */
    public function getUnpinRecentItem()
    {
        return $this->unpinRecentItem;
    }

    /**
     * method set the recent unpinned item.
     *
     * @param string $unpinRecentItem the recent unpinned item
     */
    public function setUnpinRecentItem($unpinRecentItem)
    {
        $this->unpinRecentItem = $unpinRecentItem;
    }
}
