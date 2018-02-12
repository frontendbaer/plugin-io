<?php //strict

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\CategoryDetails;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class CategoryService
 * @package IO\Services
 */
class CategoryService
{
    use RuntimeTracker;

	/**
	 * @var CategoryRepositoryContract
	 */
	private $categoryRepository;

	/**
	 * @var WebstoreConfigurationService
	 */
	private $webstoreConfig;

    /**
     * @var SessionStorageService
     */
    private $sessionStorageService;

	// is set from controllers
	/**
	 * @var Category
	 */
	private $currentCategory = null;
	/**
	 * @var array
	 */
	private $currentCategoryTree = [];

	private $currentItem = [];

    /**
     * CategoryService constructor.
     * @param CategoryRepositoryContract $category
     */
	 public function __construct(CategoryRepositoryContract $categoryRepository, WebstoreConfigurationService $webstoreConfig, SessionStorageService $sessionStorageService)
	{
	    $this->start("constructor");
		$this->categoryRepository    = $categoryRepository;
		$this->webstoreConfig 		 = $webstoreConfig;
        $this->sessionStorageService = $sessionStorageService;
	    $this->track("constructor");
	}

	/**
	 * Set the current category by ID.
	 * @param int $catID The id of the current category
	 */
	public function setCurrentCategoryID(int $catID = 0)
	{
	    $this->start("setCurrentCategoryID");
		$this->setCurrentCategory(
			$this->categoryRepository->get($catID, $this->sessionStorageService->getLang())
		);
	    $this->track("setCurrentCategoryID");
	}

	/**
	 * Set the current category by ID.
	 * @param Category $cat The current category
	 */
	public function setCurrentCategory($cat)
	{
	    $this->start("setCurrentCategory");
		$this->currentCategory     = null;
		$this->currentCategoryTree = [];

		if($cat === null)
		{
			return;
		}

		// List parent/open categories
		$this->currentCategory = $cat;
		while($cat !== null)
		{
			$this->currentCategoryTree[$cat->level] = $cat;
			$cat                                    = $this->categoryRepository->get($cat->parentCategoryId, $this->sessionStorageService->getLang());
		}
	    $this->track("setCurrentCategory");
	}

    /**
     * @return Category
     */
	public function getCurrentCategory()
    {
        return $this->currentCategory;
    }

	/**
	 * Get a category by ID
	 * @param int $catID The category ID
	 * @param string $lang The language to get the category
	 * @return Category
	 */
	public function get($catID = 0, $lang = null)
	{
	    $this->start("get");
	    if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }
		$category = $this->categoryRepository->get($catID, $lang);
	    $this->track("get");

	    return $category;
	}

	public function getChildren($categoryId, $lang = null)
    {
        $this->start("getChildren");
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }

        $result = null;
        if($categoryId > 0)
        {
            $result = $this->categoryRepository->getChildren($categoryId, $lang);
        }
        $this->track("getChildren");

        return $result;
    }
	
	/**
	 * Return the URL for a given category ID.
	 * @param Category $category the category to get the URL for
	 * @param string $lang the language to get the URL for
	 * @return string|null
	 */
	public function getURL($category, $lang = null)
	{
	    $this->start("getURL");
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }

		if(!$category instanceof Category || $category->details[0] === null)
		{
			return null;
		}
		$url = "/" . $this->categoryRepository->getUrl($category->id, $lang);
	    $this->track("getURL");

	    return $url;
	}

    /**
     * @param $category
     * @param $lang
     * @return CategoryDetails|null
     */
	public function getDetails($category, $lang)
    {
        $this->start("getDetails");
        if ( $category === null )
        {
            $this->track("getDetails");
            return null;
        }

        /** @var CategoryDetails $catDetail */
        foreach( $category->details as $catDetail )
        {
            if ( $catDetail->lang == $lang )
            {
                $this->track("getDetails");
                return $catDetail;
            }
        }

        $this->track("getDetails");
        return null;
    }


	/**
	 * Check whether a category is referenced by the current route
	 * @param int $catID The ID for the category to check
	 * @return bool
	 */
	public function isCurrent(Category $category):bool
	{
	    $this->start("isCurrent");
		if($this->currentCategory === null)
		{
			return false;
		}
		$isCurrent = $this->currentCategory->id === $category->id;
	    $this->track("isCurrent");

	    return $isCurrent;
	}

	/**
	 * Check whether any child of a category is referenced by the current route
	 * @param Category $category The category to check
	 * @return bool
	 */
	public function isOpen(Category $category):bool
	{
	    $this->start("isOpen");
		if($this->currentCategory === null)
		{
	        $this->track("isOpen");
			return false;
		}

		foreach($this->currentCategoryTree as $lvl => $categoryBranch)
		{
			if($categoryBranch->id === $category->id)
			{
	            $this->track("isOpen");
				return true;
			}
		}

        $this->track("isOpen");
		return false;
	}

	/**
	 * Check whether a category or any of its children is referenced by the current route
	 * @param Category $category The category to check
	 * @return bool
	 */
	public function isActive(Category $category = null):bool
	{
	    $this->start("isActive");
        $isActive = $category !== null && ($this->isCurrent($category) || $this->isOpen($category));
	    $this->track("isActive");

        return $isActive;
	}

    /**
     * @param Category $category
     * @param array $params
     * @param int $page
     * @return null|array
     */
    public function getItems( $category = null, array $params = [], int $page = 1 )
    {
        $this->start("getItems");
        if( $category == null )
        {
            $category = $this->currentCategory;
        }

        if( $category == null || $params == null )
        {
            $this->track("getItems");
            return null;
        }

        /**
         * @var ItemService $itemService
         */
        $itemService = pluginApp(ItemService::class);
        $result = $itemService->getItemForCategory( $category->id, $params, $page );
        $this->track("getItems");

        return $result;
    }

    /**
     * Return the sitemap tree as an array
     * @param string   $type     Only return categories of given type
     * @param string   $lang     The language to get sitemap tree for
     * @param int|null $maxLevel The deepest category level to load
     * @return array
     */
    public function getNavigationTree(string $type = "all", string $lang = null, int $maxLevel = 2):array
    {
        $this->start("getNavigationTree");
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }

        $tree = $this->categoryRepository->getLinklistTree($type, $lang, $this->webstoreConfig->getWebstoreConfig()->webstoreId, $maxLevel);
        $this->track("getNavigationTree");

        return $tree;
    }

    /**
     * Return the sitemap list as an array
     * @param string $type Only return categories of given type
     * @param string $lang The language to get sitemap list for
     * @return array
     */
    public function getNavigationList(string $type = "all", string $lang = null):array
    {
        $this->start("getNavigationList");
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }
		$list = $this->categoryRepository->getLinklistList($type, $lang, $this->webstoreConfig->getWebstoreConfig()->webstoreId);
        $this->track("getNavigationList");

        return $list;
    }

    /**
     * Returns a list of all parent categories including given category
     * @param int   $catID      The category Id to get the parents for or 0 to use current category
     * @param bool  $bottomUp   Set true to order result from bottom (deepest category) to top (= level 1)
     * @return array            The parents of the category
     */
	public function getHierarchy( int $catID = 0, bool $bottomUp = false ):array
    {
        $this->start("getHierarchy");
        if( $catID > 0 )
        {
            $this->setCurrentCategoryID( $catID );
        }

        $hierarchy = [];

        /**
         * @var Category $category
         */
        foreach ( $this->currentCategoryTree as $lvl => $category )
        {
            if( $category->linklist === 'Y' )
            {
                array_push( $hierarchy, $category );
            }
        }

        if( $bottomUp === false )
        {
            $hierarchy = array_reverse( $hierarchy );
        }

        if(count($this->currentItem))
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
            array_push( $hierarchy, $this->currentItem['texts'][$lang] );
        }

        $this->track("getHierarchy");
        return $hierarchy;
    }

    public function setCurrentItem($item)
    {
        $this->start("setCurrentItem");
        $this->currentItem = $item;
        $this->track("setCurrentItem");
    }

    public function getCurrentItem()
    {
        return $this->currentItem;
    }
}
