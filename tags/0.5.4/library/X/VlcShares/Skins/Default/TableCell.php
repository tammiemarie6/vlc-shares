<?php 

/**
 * Wrap content with a <td /> tag
 * 
 * Allowed options:
 * 		container.classes: extra classes args appended to container
 * 		container.args: extra args appended to class arg
 * 
 */
class X_VlcShares_Skins_Default_TableCell extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Wrap the content with a <td /> tag
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		$content = $this->wrap($content, 'td');
		
		return $content;
	}
	
	protected function getDefaultOptions() {

		return array_merge(parent::getDefaultOptions(), array(
		));
		
	}
	
}
