<?php

namespace zcrmsdk\crm\setup\users;

/**
 * @author sumanth-3058
 */
class ZCRMUserTheme
{
    /**
     * normal Tab Font Color.
     *
     * @var string
     */
    private $normalTabFontColor;

    /**
     * normal Tab back ground.
     *
     * @var string
     */
    private $normalTabBackground;

    /**
     * selected Tab Font Color.
     *
     * @var string
     */
    private $selectedTabFontColor;

    /**
     * selected Tab back ground.
     *
     * @var string
     */
    private $selectedTabBackground;

    private function __construct()
    {
    }

    /**
     * method to get the instance of the user theme.
     *
     * @return ZCRMUserTheme instance of the ZCRMUserTheme class
     */
    public static function getInstance()
    {
        return new ZCRMUserTheme();
    }

    /**
     * method to get the normal Tab Font Color.
     *
     * @return string the normal Tab Font Color
     */
    public function getNormalTabFontColor()
    {
        return $this->normalTabFontColor;
    }

    /**
     * method to set the normal Tab Font Color.
     *
     * @param string $normalTabFontColor the normal Tab Font Color
     */
    public function setNormalTabFontColor($normalTabFontColor)
    {
        $this->normalTabFontColor = $normalTabFontColor;
    }

    /**
     * method to get the normal Tab Background.
     *
     * @return string the normal Tab Background
     */
    public function getNormalTabBackground()
    {
        return $this->normalTabBackground;
    }

    /**
     * method to set the normal Tab Background.
     *
     * @param string $normalTabBackground the normal Tab Background
     */
    public function setNormalTabBackground($normalTabBackground)
    {
        $this->normalTabBackground = $normalTabBackground;
    }

    /**
     * method to get the selected Tab Font Color.
     *
     * @return string the selected Tab Font Color
     */
    public function getSelectedTabFontColor()
    {
        return $this->selectedTabFontColor;
    }

    /**
     * method to set the selected Tab Font Color.
     *
     * @param string $selectedTabFontColor the selected Tab Font Color
     */
    public function setSelectedTabFontColor($selectedTabFontColor)
    {
        $this->selectedTabFontColor = $selectedTabFontColor;
    }

    /**
     * method to get the selected Tab Background.
     *
     * @return string the selected Tab Background
     */
    public function getSelectedTabBackground()
    {
        return $this->selectedTabBackground;
    }

    /**
     * method to set the selected Tab Background.
     *
     * @param string $selectedTabBackground the selected Tab Background
     */
    public function setSelectedTabBackground($selectedTabBackground)
    {
        $this->selectedTabBackground = $selectedTabBackground;
    }
}
