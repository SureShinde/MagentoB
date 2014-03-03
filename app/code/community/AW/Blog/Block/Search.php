<?php

class AW_Blog_Block_Search extends AW_Blog_Block_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getPosts()
	{
        $collection = parent::_prepareCollection();
        $tag = $this->getRequest()->getParam('q');
        if ($tag) {
            $collection->getSelect()
                ->where('title LIKE "%'.$tag.'%" OR post_content LIKE "%'.$tag.'%" OR short_content LIKE "%'.$tag.'%"');
        }
        /*if ($tag) {
            $collection->addFieldToFilter(
                array(
                'rel1' => new Zend_Db_Expr('MATCH (title) AGAINST ("'.$tag.'")'),
                'rel2' => new Zend_Db_Expr('MATCH (short_content) AGAINST ("'.$tag.'")')
                )
            );

            $collection->getSelect()
                ->where('MATCH (title,short_content) AGAINST ("'.$queryText.'")')
                ->order('(rel1*1.5)+(rel2)');
        }*/
        parent::_processCollection($collection);
        return $collection;
	}

}