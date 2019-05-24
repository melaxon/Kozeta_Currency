<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Routing;

use Kozeta\Currency\Model\FactoryInterface;

class Entity
{
    /**
     * @var string
     */
    private $prefixConfigPath;
    /**
     * @var string
     */
    private $suffixConfigPath;
    /**
     * @var string
     */
    private $listKeyConfigPath;
    /**
     * @var string
     */
    private $listAction;
    /**
     * @var FactoryInterface
     */
    private $factory;
    /**
     * @var string
     */
    private $controller;
    /**
     * @var string
     */
    private $viewAction;
    /**
     * @var string
     */
    private $param;

    /**
     * @param $prefixConfigPath
     * @param $suffixConfigPath
     * @param $listKeyConfigPath
     * @param FactoryInterface $factory
     * @param $controller
     * @param string $listAction
     * @param string $viewAction
     * @param string $param
     */
    public function __construct(
        $prefixConfigPath,
        $suffixConfigPath,
        $listKeyConfigPath,
        FactoryInterface $factory,
        $controller,
        $listAction = 'index',
        $viewAction = 'view',
        $param = 'id'
    ) {
        $this->prefixConfigPath     = $prefixConfigPath;
        $this->suffixConfigPath     = $suffixConfigPath;
        $this->listKeyConfigPath    = $listKeyConfigPath;
        $this->factory              = $factory;
        $this->controller           = $controller;
        $this->listAction           = $listAction;
        $this->viewAction           = $viewAction;
        $this->param                = $param;
    }

    /**
     * @return string
     */
    public function getPrefixConfigPath()
    {
        return $this->prefixConfigPath;
    }

    /**
     * @return string
     */
    public function getSuffixConfigPath()
    {
        return $this->suffixConfigPath;
    }

    /**
     * @return string
     */
    public function getListKeyConfigPath()
    {
        return $this->listKeyConfigPath;
    }

    /**
     * @return string
     */
    public function getListAction()
    {
        return $this->listAction;
    }

    /**
     * @return FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getViewAction()
    {
        return $this->viewAction;
    }

    /**
     * @return string
     */
    public function getParam()
    {
        return $this->param;
    }
}
