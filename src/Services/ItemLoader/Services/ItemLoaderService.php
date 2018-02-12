<?php
namespace IO\Services\ItemLoader\Services;

use IO\Helper\RuntimeTracker;
use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;

/**
 * Created by ptopczewski, 09.01.17 10:47
 * Class ItemLoaderService
 * @package IO\Services\ItemLoader\Services
 */
class ItemLoaderService
{
	use LoadResultFields;
	use RuntimeTracker;

	/**
	 * @var array
	 */
	private $loaderClassList = [];

	/**
	 * @var array
	 */
	private $resultFields = [];

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @param array $loaderClassList
	 * @return $this
	 */
	public function setLoaderClassList($loaderClassList)
	{
		$this->loaderClassList = $loaderClassList;
		return $this;
	}

	/**
	 * @param array $resultFields
	 * @return $this
	 */
	public function setResultFields($resultFields)
	{
		$this->resultFields = $resultFields;
		return $this;
	}


	/**
	 * @param array $options
	 * @return $this
	 */
	public function setOptions($options)
	{
		$this->options = $options;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function load()
	{
	    $this->start("load");
		/** @var ItemLoaderFactory $itemLoaderFactory */
		$itemLoaderFactory = pluginApp(ItemLoaderFactory::class);
		$result = $itemLoaderFactory->runSearch($this->loaderClassList, $this->resultFields, $this->options);
	    $this->track("load");

	    return $result;
	}

	/**
	 * @param string $templateName
	 * @param array $loaderClassList
	 * @param array $options
	 * @return array
	 */
	public function loadForTemplate($templateName, $loaderClassList, $options = [])
	{
	    $this->start("loadForTemplate");
		$this->resultFields = $this->loadResultFields($templateName);
		$this->loaderClassList = $loaderClassList;
		$this->options = $options;
		$result = $this->load();
	    $this->track("loadForTemplate");

	    return $result;
	}
}