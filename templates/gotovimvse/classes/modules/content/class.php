<?php

class content_custom extends content {
	var $tvigle_rubric = array();
	var	$tvigle_video = array();

	public function custom_menu($id=0, $field_view = 'otobrazhat_v_verhnem_menyu', $field_sort='index_menu', $type_sort='desc') {

		$pages = new selector('pages');
		$pages->where('lang')->equals(false);
		$pages->where('hierarchy')->page($id)->childs(4);
		$pages->where($field_view)->equals(1);


		if ($type_sort!=0)
		{
			if ($type_sort==1)
			{
				$pages->order($field_sort)->asc();
			}else{
				$pages->order($field_sort)->desc();
			}

		}


		return array('items' => array('nodes:item' => $pages->result()));
	}
	/**
	 * Get module setting
	 *
	 * Checks the value of the setting of module passed in the parameter $setting.
	 *
	 * @param $setting string Name of the setting value you want to get.
	 *
	 * @return mixed Returns setting value and 0 otherwise.
	 */

	public function getModuleSetting($setting) {

		/** Instance of regedit */
		$regedit = regedit::getInstance();
		/** Value of the option */
		$result = $regedit -> getVal('//modules/content/'. $setting);

		if(!$result) {
			return 0;
		} else {
			return $result;
		}
	}


	/**
	 * Get default page ID
	 *
	 * Definition id the default page for the current language version.
	 *
	 * @return int Returns default page ID.
	 */

	public function getDefaultPageId() {

		/** The ID of the current language version */
		$curentLangId = cmsController::getInstance() -> getCurrentLang() -> getId();

		/** Instance of umiHierarchy */
		$hierarchy = umiHierarchy::getInstance();
		/** ID the default page for the current language version */
		$defaultPageId = $hierarchy -> getDefaultElementId($curentLangId);

		return $defaultPageId;
	}


	/**
	 * Get current URL
	 *
	 * Return URL of the current page.
	 *
	 * @return string Returns the URL of the current page.
	 */

	public function getCurrentUrl() {

		/** ID of the current page */
		$id = cmsController::getInstance() -> getCurrentElementId();

		if($id > 0) {
			/** Prefix of the current language version */
			$langPrefix = '/'. cmsController::getInstance() -> getCurrentLang() -> getPrefix(). '/';
			return str_replace($langPrefix, '/' ,umiHierarchy::getInstance() -> getPathById($id));
		}
	}


	/**
	 * Get near page
	 *
	 * Redirects to the first parent or the first child of the page, depending on the parameter pageId. In the absence of the desired page, redirects to the page "notfound".
	 *
	 * @param $pageId int ID page for which you want to find the parent or child.
	 * @param $type string Determines which page you want to redirect (parent or child).
	 */

	public function getNearPage($pageId, $type = 'child') {

		/** Instance of umiHierarchy */
		$h = umiHierarchy::getInstance();
		/** Page object */
		$page = $h -> getElement($pageId);

		if(is_object($page)) {
			/** Array with the id parent or child pages */
			$ids = array();

			switch($type) {
				case 'parent':
					$ids[] = $h -> getParent($pageId);
					break;
				case 'child':
					$ids = $h -> getChildIds($pageId);
					break;
			}

			if(is_array($ids)) {
				def_module::redirect($h -> getPathById(array_shift($ids)));
			}

		}

		def_module::redirect('/notfound/');
	}


	/**
	 * Get reduced information
	 *
	 * Returns reduced to a certain number of characters, the contents of the field.
	 *
	 * @param $pageId int ID page to the required field.
	 * @param $fieldIdentifier string Identifier of the field from which to take the content.
	 * @param $countCharacters int Number of characters from which to cut.
	 *
	 * @return string Abbreviated content.
	 */

	public function getReducedInformation($pageId, $fieldIdentifier, $countCharacters) {

		/** Abbreviated content */
		$truncatedString = null;
		/** Content of the field from which to take the content */
		$fieldValue = strip_tags(umiHierarchy::getInstance() -> getElement($pageId) -> getValue($fieldIdentifier));
		$truncatedString = (strlen($fieldValue) <= $countCharacters) ? $fieldValue : mb_strcut($fieldValue, 0, $countCharacters, 'utf-8');

		return $truncatedString;
	}


	/**
	 * Get optimal image params
	 *
	 * Analyzes the image parameters and returns the best depending on the desired width and height.
	 *
	 * @param $elementId int ID of element in which properties there is a field with the image.
	 * @param $field string Identifier of the field with the image.
	 * @param $maxWidth int The maximum width of the image.
	 * @param $maxHeight int The maximum height of the image.
	 *
	 * @return array Returns array with type and size of image.
	 */

	public function getOptimalImageParams($elementId, $field, $maxWidth, $maxHeight) {

		/** Object of element in which properties there is a field with the image */
		$element = umiHierarchy::getInstance() -> getElement($elementId);
		/** Type of the measured parameter */
		$type = null;
		/** Size of image */
		$size = null;

		if($element instanceOf umiHierarchyElement) {
			/** Analyzed image */
			$image = $element -> getValue($field);

			if(is_object($image)) {
				/** Analyzed image width */
				$imgWidth = $image -> getWidth();
				/** Analyzed image height */
				$imgHeight = $image -> getHeight();

				if($imgWidth >= $imgHeight) {
					$type = 'width';
					$size = $maxWidth;
				} else {
					$type = 'height';
					$size = $maxHeight;
				}
			}
		}

		return array('type' => $type, 'size' => $size);
	}

	//Выводит список ингредиентов рецепта (в админ панели)
	public function recipe_ingredients($recipe_id=false, $do = false, $id_ingredient_pp = false, $amount = false){
		$recipe_id = $this->first_id($recipe_id);
		$hierarchy = umiHierarchy::getInstance();
		$oC = umiObjectsCollection::getInstance();

		$ingredients_arr = $this -> full_list_ingredients();

		//Выполнение действия $do
		if ($do){
			//Прописание новых порядковых номеров
			$pages = new selector('objects');
            $pages -> types('object-type')->id(70);
			$pages -> where('rid')->equals($recipe_id);
			$pages -> order('npp');
			$result = $pages -> result();
			for($i=0; $i<count($result); $i++){
				$oC -> getObject($result[$i]->id) -> setValue('npp',$i);
				$oC -> getObject($result[$i]->id) -> commit();
			}

			if ($do == 'shift'){
				$pages = new selector('objects');
				$pages -> types('object-type')->id(70);
				$pages -> where('rid')->equals($recipe_id);
				$pages -> order('npp');
				$pages -> where('npp')->equals(array(($id_ingredient_pp-1),$id_ingredient_pp));
				$result = $pages -> result();
				$obj_0_id = $result[0]->id;
				$obj_1_id = $result[1]->id;
				$obj_1_npp = $oC -> getObject($obj_1_id) -> getValue('npp');
				$oC -> getObject($obj_0_id) -> setValue('npp',$obj_1_npp);
				$oC -> getObject($obj_1_id) -> setValue('npp',($obj_1_npp-1));
				$oC -> getObject($obj_0_id) -> commit();
				$oC -> getObject($obj_1_id) -> commit();
			}
			if ($do == 'del'){
				$pages = new selector('objects');
				$pages -> types('object-type')->id(70);
				$pages -> where('rid')->equals($recipe_id);
				$pages -> order('npp');
				$pages -> where('npp')->equals($id_ingredient_pp);
				$result = $pages -> result();
				$oC -> delObject($result[0]->id);
				$this->set_components_recipe($recipe_id);
			}
			if ($do == 'change'){
				$pages = new selector('objects');
				$pages -> types('object-type')->id(70);
				$pages -> where('rid')->equals($recipe_id);
				$pages -> order('npp');
				$pages -> where('npp')->equals($id_ingredient_pp);
				$result = $pages -> result();
				$oC -> getObject($result[0]->id) -> setValue('amount',($amount/100));
				$oC -> getObject($result[0]->id) -> commit();
				$this->set_components_recipe($recipe_id);
			}
			if ($do == 'change_koef'){
				$pages = new selector('objects');
				$pages -> types('object-type')->id(70);
				$pages -> where('rid')->equals($recipe_id);
				$pages -> order('npp');
				$pages -> where('npp')->equals($id_ingredient_pp);
				$result = $pages -> result();
				$oC -> getObject($result[0]->id) -> setValue('koef_in_recipe',$amount);
				$oC -> getObject($result[0]->id) -> commit();
				$this->set_components_recipe($recipe_id);
			}
			if ($do == 'c_rr'){
				$pages = new selector('objects');
				$pages -> types('object-type')->id(70);
				$pages -> where('rid')->equals($recipe_id);
				$pages -> order('npp');
				$pages -> where('npp')->equals($id_ingredient_pp);
				$result = $pages -> result();
				if ($amount==1)	$oC -> getObject($result[0]->id) -> setValue('caloric_in_ready_recipe',true);
				else 			$oC -> getObject($result[0]->id) -> setValue('caloric_in_ready_recipe',false);
				$oC -> getObject($result[0]->id) -> commit();
				$this->set_components_recipe($recipe_id);
			}
			if ($do == 'add'){
				$get_obj_id = $oC -> addObject("", 70);
				$get_obj = $oC -> getObject($get_obj_id);
				$get_obj -> setValue('rid',$recipe_id);
				$get_obj -> setValue('pid',$id_ingredient_pp);
				$get_obj -> setValue('amount',$amount);
				$get_obj -> setValue('npp',1000);
				$get_obj -> commit();
				$this->set_components_recipe($recipe_id);
			}
			//Прописание новых порядковых номеров
			$pages = new selector('objects');
			$pages -> types('object-type')->id(70);
			$pages -> where('rid')->equals($recipe_id);
			$pages -> order('npp');
			$result = $pages -> result();
			for($i=0; $i<count($result); $i++){
				$oC -> getObject($result[$i]->id) -> setValue('npp',$i);
				$oC -> getObject($result[$i]->id) -> commit();
			}
		}

		$pages = new selector('objects');
		$pages -> types('object-type')->id(70);
		$pages -> where('rid')->equals($recipe_id);
		$pages -> order('npp');
		$result = $pages -> result();

		$content = "";
		$i = 0;
		foreach ($result as $obj){
			$ingred = $hierarchy->getElement($obj->getValue('pid'));
			if (!$ingred instanceof umiHierarchyElement) continue;

			$amount = ($obj->getValue("amount"))*100;
			$koef_in_recipe = $obj->getValue("koef_in_recipe");
			$caloric_in_ready_recipe = $obj->getValue("caloric_in_ready_recipe");
			if ($caloric_in_ready_recipe) $checked = "checked"; else $checked = "";
			$class = " row ";
			$is_active = $ingred->getIsActive();
			if (!$is_active) $class.="hide";
			$content.="<tr class='".$class."' id='".$i++."'>";
			$content.="<td><a href='/admin/content/edit/".$obj->getValue('pid')."/'>".$ingred->getName().", 1".$ingred->ed."= ".(($ingred->koef)*100)."гр.</a></td>";
			$content.="<td><input class='amount' type='text' value='".$amount."' /> г</td>";
			$content.="<td>".round(($amount/(100*($ingred->koef))),2)." ".$ingred->ed."</td>";
			$content.="<td><input class='koef_in_recipe' type='text' value='".$koef_in_recipe."' /> % в рецепте</td>";
			$content.="<td><input class='caloric_in_ready_recipe' type='checkbox' ".$checked." /> расчет ккал в пригот.продукте</td>";

			$content.="<td><img class='del_item' src='/images/cms/admin/mac/tree/ico_del.png' border='0' title='Удалить из списка' /></td>";
			if ($i!=1)	$content.="<td><img class='shift_item' src='/images/cms/admin/arrow_up.png' border='0' title='На уровень выше' /></td>";
			else $content.="<td></td>";
			$content.="</tr>";
		}
		$content.="<tr id='add'>";
		$content.="<td><select>";

		foreach ($ingredients_arr as $id_item=>$item){
			$content.="<option value='".$id_item."'>".$item."</option>";
		}
		$content.="</select></td>";
		//$content.="<td><input  type='text' value='0' /> г</td>";
		$content.="<td style='text-align:left; background-color:#FFFFFF;'><img class='add_item' src='/images/cms/admin/mac/tree/ico_add.png' border='0' title='Добавить ингредиент' /></td>";
		$content.="</tr>";
		$this -> recipes_recount($recipe_id);
		return $content;
	}

	//Список всех ингредиентов
	public function full_list_ingredients(){
		$pages = new selector('pages');
		$pages -> types('object-type')->id(67);
		$result = $pages -> result();
		$ingredients_arr = array();
		foreach ($result as $obj){
			$ingredients_arr[$obj->id] = mb_strtolower($obj->getName(),'UTF-8').", ".mb_strtolower($obj->ed,'UTF-8');
		}
		asort($ingredients_arr);
		return $ingredients_arr;
	}

	//Список элементов категории
	public function getElementsContent($id_parent=2, $type_id = 66, $paginator = true, $limit = 10, $childs = 1, $order = 'time'){
		$h = umiHierarchy::getInstance();
		$p = (int) getRequest('p');

		if ($p<0) $p=0;
		$general_page = umiHierarchy::getInstance()->getElement(1);
		$num_on_page = $general_page->getValue('recipes_per_page');
		$max_rating = $general_page->getValue('max_rating');
		$pages = new selector('pages');
		$pages->types('object-type')->id($type_id);
		$pages->where('hierarchy')->page($id_parent)->childs($childs);
        if (cmsController::getInstance()->getModule('users')->user_id == 2) $pages->where('is_active')->equals(array(0,1));

		//Парсинг фильтров из адресной строки
		$fields_filter = getRequest('fields_filter');
		if ($fields_filter)
			foreach($fields_filter as $filter=>$value){
				if (isset($value['lt'])){
					if ($value['lt'])
						$pages->where($filter)->eqless($value['lt']);
				} else
					if (isset($value['gt'])){
						if ($value['gt'])
							$pages->where($filter)->eqmore($value['gt']);
					} else
						$pages->where($filter)->equals($value);
			}

		$pages->order($order)->desc();
		if ($paginator) $pages->limit($p*$num_on_page,$num_on_page);
		else 			$pages->limit(0,$limit);
		$result = $pages->result();
		$sum = array();
		foreach($result as $obj){
			$arr = array();
			$id = $this->first_id($obj->id);
			$element = $h->getElement($id, true);
			$arr['attribute:id'] = $id;
			$arr['attribute:old_id'] = $element->id_old_base;
			$arr['attribute:name'] = $element->h1;
			$arr['attribute:link'] = $element->link;
			$arr['attribute:rating'] = round(5*(($element->rating)/$max_rating),1);
			$arr['attribute:prep_time'] = $element->prep_time;
			$arr['attribute:prep_time_tr'] = $this->tr_time($element->prep_time);
			$arr['attribute:cooking_time'] = $element->cooking_time;
			$arr['attribute:cooking_time_tr'] = $this->tr_time($element->cooking_time);
			$arr['attribute:total_time'] = $element->prep_time + $element->cooking_time;
			$arr['attribute:total_time_tr'] = $this->tr_time($element->prep_time + $element->cooking_time);
			$arr['attribute:views'] = is_numeric($element->views) ? $element->views : 0;
			$arr['attribute:likes'] = is_numeric($element->likes) ? $element->likes : 0;
			$arr['attribute:num_servings'] = $element->num_servings;
			$arr['attribute:publish_time'] = umiDate::getTimeStamp($element->time);
			$arr['anons'] = $element->meta_descriptions;
			$sum[]=$arr;
		}
		$result = array("nodes:item"=>$sum, "per_page"=>$num_on_page, "total"=>$pages->length());
		return $result;
	}

	//Список последний публикаций
	public function getLastElements($type_id = 66, $limit = 10, $parent_id = 24911, $filter_field = false, $filter_value = "isnull", $actual = false){
		$h = umiHierarchy::getInstance();
		$general_page = umiHierarchy::getInstance()->getElement(1);
		$num_on_page = $general_page->getValue('recipes_per_page');
		$max_rating = $general_page->getValue('max_rating');
		$pages = new selector('pages');
		$pages->types('object-type')->id($type_id);
		$pages->where('hierarchy')->page($parent_id)->childs(3);
        if ($actual) $pages->where('out_of_date')->notequals(true);
        if ($filter_field and ($filter_value == "isnull")) $pages->where('pos')->isnull(true);
        if ($filter_field and ($filter_value == "isnotnull")) $pages->where('pos')->isnotnull(false);
        if ($filter_field and ($filter_value != "isnull") and ($filter_value != "isnotnull")) $pages->where($filter_field)->equals($filter_value);
        $pages->order('time')->desc();
		$pages->limit(0,$limit*3);
		$result = $pages->result();
		$sum = array();
		$already = array();
		foreach($result as $obj){
			$arr = array();
			$id = $this->first_id($obj->id);
			if (in_array($id, $already)) continue;
			$already[] = $id;
			$element = $h -> getElement($id, true);
			$arr['attribute:id'] = $id;
			$arr['attribute:old_id'] = $element->id_old_base;
			$arr['name'] = $element->h1;
			$arr['attribute:link'] = $element->link;
            if ($element->rating) $arr['attribute:rating'] = round(5*(($element->rating)/$max_rating),1);
            if ($element->prep_time){
                $arr['attribute:prep_time'] = $element->prep_time;
                $arr['attribute:prep_time_tr'] = $this->tr_time($element->prep_time);
            }
            if ($element->cooking_time){
                $arr['attribute:cooking_time'] = $element->cooking_time;
                $arr['attribute:cooking_time_tr'] = $this->tr_time($element->cooking_time);
            }
            if ($element->prep_time and $element->cooking_time){
                $arr['attribute:total_time'] = $element->prep_time + $element->cooking_time;
                $arr['attribute:total_time_tr'] = $this->tr_time($element->prep_time + $element->cooking_time);
            }
            if ($element->views) $arr['attribute:views'] = is_numeric($element->views) ? $element->views : 0;
            if ($element->likes) $arr['attribute:likes'] = is_numeric($element->likes) ? $element->likes : 0;
            if ($element->time) $arr['attribute:publish_time'] = umiDate::getTimeStamp($element->time);
            if ($element->out_of_date) $arr['attribute:out_of_date'] = $element->out_of_date;
			$arr['descriptions'] = $element->meta_descriptions;
			$sum[]=$arr;
			if (count($already) == $limit) break;
		}
		$result = array("nodes:item"=>$sum, "total"=>$pages->length());
		return $result;
	}

	//Список продуктов в рецепте
	public function receipt_prods($rid=false, $portions = false) {
		$id = $this->first_id($rid);
		$oc = umiObjectsCollection::getInstance();
		$h = umiHierarchy::getInstance();
		$objs = new selector('objects');
		$objs->types('object-type')->id(70);
		$objs->where('rid')->equals($id);
		$objs->order('npp');
		$items = array();
		$pollsGlasMedia = array();
		$getElement = $h->getElement($id);
        if ($getElement instanceof umiHierarchyElement){
            $portions_recipe = $getElement->num_servings;
            if (!$portions_recipe) $portions_recipe = 1;
            $portions = is_numeric($portions) ? $portions : false;
            $price = 0;
            $weight = 0;
            if ($portions) $koef = $portions / $portions_recipe; else $koef = 1;

            if (isset($_SESSION['id_currency'])){
                $listCurrency = $this->listCurrency($_SESSION['id_currency'], true);
                foreach($listCurrency as $currency){
                    if (isset($currency['default']))
                        if ($currency['default'] == 1){
                            $koef_price = $currency['exchange'];
                            break;
                        }
                }
            } else{
                $sel_cur = new selector('objects');
                $sel_cur -> types('object-type')->id(79);
                $sel_cur = $sel_cur -> result();
                foreach($sel_cur as $sel_cur_){
                    if ($sel_cur_->default == 1){
                        $koef_price = $sel_cur_->exchange;
                        break;
                    }
                }
            }

            foreach ($objs as $obj) {
                $pel = $h->getElement($obj->pid);
                if ($pel instanceof umiHierarchyElement){
                    $arr = array();
                    $arr['attribute:id'] = $pel->id;
                    $arr['attribute:link'] = $pel->link;
                    $arr['attribute:ingredient'] = $pel->getName();
                    if ($pel->ed){
                        $arr['attribute:ed'] = $pel->ed;
                        $arr['attribute:amount'] = round($koef * ($obj->amount) / ($pel->koef),$pel->rounding);
                        $arr['attribute:amount_g'] = number_format( $koef * 100*$obj->amount, 0, ',', ' ' );
                        $amoung_g = $koef * 100*$obj->amount;
                        $weight += $amoung_g;
                        $arr['attribute:price'] = $koef_price * $koef * $pel->price * $obj->amount;
                    }
                    else {
                        $arr['attribute:ed'] = "г";
                        $arr['attribute:amount'] = number_format( $koef * 100*$obj->amount, 0, ',', ' ' );
                        $amoung_g = $koef * 100*$obj->amount;
                        $weight += $amoung_g;
                        $arr['attribute:price'] = $koef_price * $koef * $pel->price * $obj->amount;
                    }
                    $items[] = $arr;
                    $price += $arr['attribute:price'];

					if ($pel -> getValue('title_glas_media') && $pel -> getValue('url_glas_media'))
						$pollsGlasMedia[] = array("title"=>$pel -> getValue('title_glas_media'), "url"=>$pel -> getValue('url_glas_media'));
				}
            }
            if ($portions) $weight_portion = $weight / $portions; else $weight_portion = $weight / $portions_recipe;

			return array("items"=>array("nodes:item"=>$items), "price" => round($price,2), "weight" => round($weight), "weight_portion" => round($weight_portion), "polls_glas_media"=>array("nodes:poll"=>$pollsGlasMedia));
        }
        return;
	}

	//Список категорий и рецептов для Корень категорий рецептов
	public function root_category_recipes($per_category = 10){
		if (strpos($_SERVER['REQUEST_URI'],"?")) $path = "?".end(explode("?",$_SERVER['REQUEST_URI'])); else $path="";
		$h = umiHierarchy::getInstance();
		$pages = new selector('pages');
		$pages->types('object-type')->id(65);
		$pages->where('hierarchy')->page('/cook/rec/')->childs(1);
		$result = $pages->result();
		$rubric = array();
		$recipe_exist = array();
		foreach($result as $obj){
			$rubric_item = array();
			$rubric_item['attribute:id'] = $obj->id;
			$rubric_item['attribute:link'] = $obj->link.$path;
			$rubric_item['name'] = $obj->h1;
			$items = new selector('pages');
			$items->types('object-type')->id(66);
			$items->where('hierarchy')->page($obj->id)->childs(1);
			//Парсинг фильтров из адресной строки
			$fields_filter = getRequest('fields_filter');
			if ($fields_filter)
				foreach($fields_filter as $filter=>$value){
					if (isset($value['lt'])){
						if ($value['lt'])
							$items->where($filter)->eqless($value['lt']);
					} else
						if (isset($value['gt'])){
							if ($value['gt'])
								$items->where($filter)->eqmore($value['gt']);
						} else
							$items->where($filter)->equals($value);
				}
			$items->order('time')->desc();
			$items->limit(0,10);
			$res_items = $items->result();
			$first_recipes = array();
			$i = 0;
			foreach($res_items as $item){
				if ($i == $per_category) break;
				if (in_array($this->first_id($item->id), $recipe_exist)) continue;
				$element = $h -> getElement($this->first_id($item->id));
				$item_param = array();
				$item_param['attribute:id'] = $element->id;
				$item_param['attribute:old_id'] = $element->id_old_base;
				$item_param['attribute:link'] = $element->link;
				$item_param['name'] = $element->h1;
				$item_param['descriptions'] = $element->meta_descriptions;
				$first_recipes[] = $item_param;
				$recipe_exist[] = $this->first_id($element->id);
				$i++;
			}
			$rubric_item["recipes"] = array("nodes:item"=>$first_recipes);
			$rubric[] = $rubric_item;
		}
		return array("nodes:rubric"=>$rubric);
	}

	//Список категорий
	public function listCategories($type_id = 65, $parent_id = 0, $per_category = false){
		$p = is_numeric(getRequest('p')) ? getRequest('p') : 0;
		if (strpos($_SERVER['REQUEST_URI'],"?")) $path = "?".end(explode("?",$_SERVER['REQUEST_URI'])); else $path="";
		$pages = new selector('pages');
		$pages->types('object-type')->id($type_id);
		$pages->where('hierarchy')->page($parent_id)->childs(1);
		$result = $pages->result();
		$total = $pages->length;
		$total_pages = 0;
		if ($per_category)
		if (is_numeric($per_category) and $per_category >0){
			$total_pages = ceil($total/$per_category);
			$result = array_slice($result, $p*$per_category, $per_category);
		}
		$rubric = array();
		foreach($result as $obj){
			$rubric_item = array();
			$rubric_item['attribute:id'] = $obj->id;
			$rubric_item['attribute:link'] = $obj->link;
			$rubric_item['attribute:link_uri'] = $obj->link.$path;
			$rubric_item['name'] = $obj->h1;
			$rubric[] = $rubric_item;
		}
		return array("nodes:item"=>$rubric, "total"=>$total, "total_pages"=>$total_pages, "current_page"=>$p);
	}

	//Список страниц категории
	public function listElements($type_id = 73, $parent_id = 0, $per_category = false, $sort = false, $sort_desc = false, $need = 1, $hidden = false){
		$p = is_numeric(getRequest('p')) ? getRequest('p') : 0;
		$h = umiHierarchy::getInstance();
		$items = new selector('pages');
		$items->types('object-type')->id($type_id);
		$items->where('hierarchy')->page($parent_id)->childs($need);
        if ($hidden == "1") $items->where('is_active')->equals(array(0,1));
		//Парсинг фильтров из адресной строки
		$fields_filter = getRequest('fields_filter');
		if ($fields_filter)
			foreach($fields_filter as $filter=>$value){
				if (isset($value['lt'])){
					if ($value['lt'])
						$items->where($filter)->eqless($value['lt']);
				} else
					if (isset($value['gt'])){
						if ($value['gt'])
							$items->where($filter)->eqmore($value['gt']);
					} else
						$items->where($filter)->equals($value);
			}
		if ($sort){
			if ($sort_desc)	$items->order($sort)->desc(); else $items->order($sort)->asc();
		}
		$result = $items->result();
		$total = $items->length;
		$total_pages = ceil($total/$per_category);
		if ($per_category) if (is_numeric($per_category)) $result = array_slice($result, $p*$per_category, $per_category);

		$elements = array();
		foreach($result as $item){
			$element = $h -> getElement($item->id);
			$item_param = array();
			$item_param['attribute:id'] = $element->id;
			if ($element->id_old_base) $item_param['attribute:old_id'] = $element->id_old_base;
			$item_param['attribute:link'] = $element->link;
			$item_param['name'] = $element->h1;
			$item_param['descriptions'] = $element->meta_descriptions;
            if ($element->getIsActive()) $item_param['attribute:is-active'] = 1; else $item_param['attribute:is-active'] = 0;
			$elements[] = $item_param;
		}
		return array("nodes:item"=>$elements, "total"=>$total, "total_pages"=>$total_pages, "per_page"=> $per_category,"current_page"=>$p);
	}



	//Список категорий и статей для Корень категорий статей
	public function root_category_articles($parent_id = false, $per_category = 10, $type_id_category = 72, $type_id_article = 73, $order = 'time'){
		$h = umiHierarchy::getInstance();
		$pages = new selector('pages');
		$pages->types('object-type')->id($type_id_category);
		if ($parent_id) $pages->where('hierarchy')->page($parent_id)->childs(3);
		$result = $pages->result();
		$rubric = array();
		$recipe_exist = array();
		foreach($result as $obj){
			$rubric_item = array();
			$rubric_item['attribute:id'] = $obj->id;
			$rubric_item['attribute:link'] = $obj->link;
			$rubric_item['name'] = $obj->h1;
			$items = new selector('pages');
			$items->types('object-type')->id($type_id_article);
			$items->where('hierarchy')->page($obj->id)->childs(1);
			if ($order) $items->order($order)->desc();
			$items->limit(0,$per_category);
			$res_items = $items->result();
			$first_recipes = array();
			$i = 0;
			foreach($res_items as $item){
				if ($i == $per_category) break;
				if (in_array($this->first_id($item->id), $recipe_exist)) continue;
				$element = $h -> getElement($this->first_id($item->id));
				$item_param = array();
				$item_param['attribute:id'] = $element->id;
				$item_param['attribute:old_id'] = $element->id_old_base;
				$item_param['attribute:link'] = $element->link;
				$item_param['name'] = $element->h1;
				$item_param['descriptions'] = $element->meta_descriptions;
				$first_recipes[] = $item_param;
				$recipe_exist[] = $this->first_id($item->id);
				$i++;
			}
			$rubric_item["recipes"] = array("nodes:item"=>$first_recipes);
			$rubric[] = $rubric_item;
		}
		return array("nodes:rubric"=>$rubric);
	}

	//Список категорий tvigle видео
	public function root_category_tvigle($parent_id = 3, $per_category = 10){
		$pages = new selector('pages');
		$pages->types('object-type')->id(76);
		$pages->where('hierarchy')->page($parent_id)->childs(1);
		$result = $pages->result();
		$rubric = array();
		foreach($result as $obj){
			$rubric_item = array();
			$rubric_item['attribute:id'] = $obj->id;
			$rubric_item['attribute:link'] = $obj->link;
			$rubric_item['name'] = $obj->h1;
			$items = new selector('pages');
			$items->types('object-type')->id(77);
			$items->where('hierarchy')->page($obj->id)->childs(5);
			$items->order('date')->desc();
			$items->limit(0,$per_category);
			$res_items = $items->result();
			$first_videos = array();
			foreach($res_items as $item){
				$item_param = array();
				$item_param['attribute:id'] = $item->id;
				$item_param['attribute:link'] = $item->link;
				$item_param['attribute:img'] = $item->img;
				$item_param['attribute:img_300'] = 	str_replace(".jpg","_300.jpg",$item->img);
				$item_param['name'] = $item->h1;
				$item_param['descriptions'] = $item->anons;
				$first_videos[] = $item_param;
			}
			$rubric_item["videos"] = array("nodes:item"=>$first_videos);
			$rubric[] = $rubric_item;
		}
		return array("nodes:rubric"=>$rubric);
	}

	//Список последних видео tvigle
	public function last_video_tvigle($limit = 10){
		$pages = new selector('pages');
		$pages->types('object-type')->id(77);
		$pages->where('hierarchy')->page('/cook/video/')->childs(3);
		$pages->order('rand');
		$pages->limit(0, $limit);
		$result = $pages->result();
		$video = array();
		foreach($result as $item){
			$item_param = array();
			$item_param['attribute:id'] = $item->id;
			$item_param['attribute:link'] = $item->link;
			$item_param['attribute:img'] = $item->img;
			$item_param['attribute:img_300'] = 	str_replace(".jpg","_300.jpg",$item->img);
			$item_param['attribute:date'] = $item->date;
			$item_param['attribute:id_tvigle'] = $item->id_tvigle;
			$item_param['name'] = $item->h1;
			$item_param['descriptions'] = $item->anons;
			$video[] = $item_param;
		}
		return array("nodes:video"=>$video);
	}

	//Определение страницы с которой была сделана виртуальная копия
	public function first_id($id, $list = false) {
		$page_id = (int) $id;
		$h = umiHierarchy::getInstance();
		//экземпляр страницы
		$element = $h->getElement($page_id);
		if($element){
			// object_id источника данных
			$object_id = $element->getObjectId();
			// список всех страниц, которые используют данный объект
			$arr_id = $h->getObjectInstances($object_id);
			// первая страница, это страница с наименьшим page_id, т.е. исходная страница
			if ($list) return $arr_id;
			return $arr_id[0];
		}else  return $page_id;
	}

	//Загрузка изображения на сервер
	public function upload_img(){
		$root = CURRENT_WORKING_DIR;
		$page_id = is_numeric(getRequest('page_id')) ? getRequest('page_id') : false;
        $path = getRequest('path');
        if (!$path) $path = "/recipes/";
		if (!$page_id) return "";

		$edit_page_id = $page_id;
		$hierarchy = umiHierarchy::getInstance();
		$element = $hierarchy -> getElement($page_id);
		if ($element instanceOf umiHierarchyElement)	if ($element->id_old_base) $edit_page_id = $element->id_old_base;

		$dir = $root.$path.$edit_page_id."/";
		if (!file_exists($dir)) mkdir($dir);

		$valid_types =  array("gif","jpg", "png", "jpeg","GIF","JPG", "PNG", "JPEG");

		//Проверка, действительно ли загруженный файл является изображением
		$imageinfo = getimagesize($_FILES["select_photo"]["tmp_name"]);
		if($imageinfo["mime"] != "image/gif" && $imageinfo["mime"] != "image/jpeg" && $imageinfo["mime"] !="image/png") {
			$buffer = outputBuffer::current();
			$buffer->charset('utf-8');
			$buffer->contentType('text/plane');
			$buffer->clear();
			$buffer->push("not_img");
			$buffer->end();
		}
		//Сохранение загруженного изображения с расширением, которое возвращает функция getimagesize()
		//Расширение изображения
		$mime=explode("/",$imageinfo["mime"]);
		//Имя файла
		$namefile=explode(".",$_FILES["select_photo"]["name"]);
		$name_new = "upload";

		//Определение ширины и высоты изображения
		$get_width = $imageinfo[0];
		$get_height = $imageinfo[1];
		$set_width = false;
		$set_height = false;
		if ($get_width>=$get_height){
			if ($get_width>2000) $set_width = 2000; else $set_width = $get_width;
		} else {
			if ($get_height>2000) $set_height = 2000; else $set_height = $get_height;
		}


		//Функция, перемещает файл из временной, в указанную вами папку
		if (move_uploaded_file($_FILES["select_photo"]["tmp_name"], $dir.$name_new.".".strtolower($namefile[1]))) {
			$this->resize($dir.$name_new.".".strtolower($namefile[1]), $dir."upload.jpg", $set_width, $set_height);
			$buffer = outputBuffer::current();
			$buffer->charset('utf-8');
			$buffer->contentType('text/plane');
			$buffer->clear();
			$buffer->push($path.$edit_page_id."/upload.jpg");
			$buffer->end();
		}else{
			$buffer = outputBuffer::current();
			$buffer->charset('utf-8');
			$buffer->contentType('text/plane');
			$buffer->clear();
			$buffer->push("error");
			$buffer->end();
		}
	}

    //Сохранение изображения с Интернета
    public function upload_url_img($page_id = false, $url = false, $path = false){
        $root = CURRENT_WORKING_DIR;
        $page_id = is_numeric($page_id) ? $page_id : false;
        if (!$page_id) return "";
        $img_size = getimagesize ($url);
        if (!$img_size) return "";

        //Определение ширины и высоты изображения
        $get_width = $img_size[0];
        $get_height = $img_size[1];
        $set_width = false;
        $set_height = false;
        if ($get_width>=$get_height){
            if ($get_width>2000) $set_width = 2000; else $set_width = $get_width;
        } else {
            if ($get_height>2000) $set_height = 2000; else $set_height = $get_height;
        }

        if (!$path) $path = "/recipes/";
        $dir = $root.$path.$page_id."/";
        if (!file_exists($dir)) mkdir($dir);
        $this->resize($url, $dir."upload.jpg", $set_width, $set_height);
        $buffer = outputBuffer::current();
        $buffer->charset('utf-8');
        $buffer->contentType('text/plane');
        $buffer->clear();
        $buffer->push($path.$page_id."/upload.jpg");
        $buffer->end();
    }

	//Сохранение отредактированного изображения
	public function saveImage($path = "/recipes/"){
		$page_id = getRequest('page_id');
		if (!$page_id) die;
		$crop_x = getRequest('crop_x');
		$crop_y = getRequest('crop_y');
		$crop_width_ = getRequest('crop_width_');
		$crop_height_ = getRequest('crop_height_');
		$width_ = getRequest('width_');
		$height_ = getRequest('height_');
		$mode_img = getRequest('mode_img');

		$root = CURRENT_WORKING_DIR;
		$page_id = is_numeric(getRequest('page_id')) ? getRequest('page_id') : false;
		if (!$page_id) die;

		$edit_page_id = $page_id;
		$hierarchy = umiHierarchy::getInstance();
		$element = $hierarchy -> getElement($page_id);
		if ($element instanceOf umiHierarchyElement)	if ($element->id_old_base) $edit_page_id = $element->id_old_base;

		$dir = $root.$path.$edit_page_id."/";

		switch($mode_img) {
			case 'general':
				$this->resize($dir."upload.jpg",$dir."img_600.jpg",$width_,$height_);
				$this->crop($dir."img_600.jpg",$dir."img_600.jpg",array($crop_x,$crop_y,$crop_x+$crop_width_,$crop_y+$crop_height_));
				$this->resize($dir."img_600.jpg",$dir."img_500.jpg",500,333);
				$this->resize($dir."img_600.jpg",$dir."img_250.jpg",250,166);
				$this->resize($dir."img_600.jpg",$dir."img_200.jpg",200,133);
				break;
			case 'banner':
				$this->resize($dir."upload.jpg",$dir."banner.jpg",$width_,$height_);
				$this->crop($dir."banner.jpg",$dir."banner.jpg",array($crop_x,$crop_y,$crop_x+$crop_width_,$crop_y+$crop_height_));
				break;
			case 'wide_banner':
				$this->resize($dir."upload.jpg",$dir."wide_banner.jpg",$width_,$height_);
				$this->crop($dir."wide_banner.jpg",$dir."wide_banner.jpg",array($crop_x,$crop_y,$crop_x+$crop_width_,$crop_y+$crop_height_));
                $this->resize($dir."wide_banner.jpg",$dir."wide_banner_450.jpg",450, 192);
				break;
			case 'stage':
                $uniqFN = uniqid("");
                $this->resize($dir."upload.jpg",$dir.$uniqFN.".jpg",$width_,$height_);
                $this->crop($dir.$uniqFN.".jpg",$dir.$uniqFN.".jpg",array($crop_x,$crop_y,$crop_x+$crop_width_,$crop_y+$crop_height_));
				break;
			default:
                $uniqFN = uniqid("");
                $this->resize($dir."upload.jpg",$dir.$uniqFN.".jpg",$width_,$height_);
                $this->crop($dir.$uniqFN.".jpg",$dir.$uniqFN.".jpg",array($crop_x,$crop_y,$crop_x+$crop_width_,$crop_y+$crop_height_));
                break;
		}
	}

	//Изменение размеров изображения
	public function resize($file_input, $file_output, $w_o, $h_o, $percent = false) {
		list($w_i, $h_i, $type) = getimagesize($file_input);
		if (!$w_i || !$h_i) {
			echo 'Невозможно получить длину и ширину изображения';
			return;
		}
		$types = array('','gif','jpeg','png');
		$ext = $types[$type];
		if ($ext) {
			$func = 'imagecreatefrom'.$ext;
			$img = $func($file_input);
		} else {
			echo 'Некорректный формат файла';
			return;
		}
		if ($percent) {
			$w_o *= $w_i / 100;
			$h_o *= $h_i / 100;
		}
		if (!$h_o) $h_o = $w_o/($w_i/$h_i);
		if (!$w_o) $w_o = $h_o/($h_i/$w_i);

		$img_o = imagecreatetruecolor($w_o, $h_o);
		imagecopyresampled($img_o, $img, 0, 0, 0, 0, $w_o, $h_o, $w_i, $h_i);
		if ($type == 2) {
			return imagejpeg($img_o,$file_output,90);
		} else {
			$func = 'image'.$ext;
			return $func($img_o,$file_output);
		}
	}

	public function crop($file_input, $file_output, $crop = 'square',$percent = false) {
		list($w_i, $h_i, $type) = getimagesize($file_input);
		if (!$w_i || !$h_i) {
			echo 'Невозможно получить длину и ширину изображения';
			return;
		}
		$types = array('','gif','jpeg','png');
		$ext = $types[$type];
		if ($ext) {
			$func = 'imagecreatefrom'.$ext;
			$img = $func($file_input);
		} else {
			echo 'Некорректный формат файла';
			return;
		}
		if ($crop == 'square') {
			$min = $w_i;
			if ($w_i > $h_i) $min = $h_i;
			$w_o = $h_o = $min;
		} if ($crop == '3:2'){
			if (2*$w_i/3 <= $h_i) {
				$w_o = $w_i;
				$h_o = round($w_i * 2 / 3);
				$x_o = 0;
				$y_o = round(($h_i - $h_o) / 2);
			} else {
				$h_o = $h_i;
				$w_o = round($h_i * 3 / 2);
				$x_o = round(($w_i - $w_o) / 2);
				$y_o = 0;
			}

		} if ($crop == "last_lines") {
			$w_o = $w_i;
			$h_o = 1;
			$x_o = 0;
			$y_o = $h_i - 1;
		} else {
			list($x_o, $y_o, $w_o, $h_o) = $crop;
			if ($percent) {
				$w_o *= $w_i / 100;
				$h_o *= $h_i / 100;
				$x_o *= $w_i / 100;
				$y_o *= $h_i / 100;
			}
			if ($w_o < 0) $w_o += $w_i;
			$w_o -= $x_o;
			if ($h_o < 0) $h_o += $h_i;
			$h_o -= $y_o;
		}
		$img_o = imagecreatetruecolor($w_o, $h_o);
		imagecopy($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o);
		if ($type == 2) {
			return imagejpeg($img_o,$file_output,90);
		} else {
			$func = 'image'.$ext;
			return $func($img_o,$file_output);
		}
	}

	//Проверка наличия каталога для редактирования рецепта (создание)
	public function verifyEditRecipe($user_id = false, $recipe_id = false){
		$root = CURRENT_WORKING_DIR;
		$user_id = is_numeric($user_id) ? $user_id : 0;
		$recipe_id = is_numeric($recipe_id) ? $recipe_id : 0;
		//Получаем id текущего пользователя
		$permissions = permissionsCollection::getInstance();
		$currentUserId = $permissions->getUserId();
		if ($user_id == $currentUserId) {
			if (($recipe_id != 0) and ($recipe_id > 0))
				if (file_exists($root."/recipes_user/".$currentUserId."/".$recipe_id)) return array("return"=>$recipe_id);
			//Проверка свободного каталога
			for($i = 1; $i<1000; $i++){
				if (!file_exists($root."/recipes_user/".$currentUserId."/".$i)) {
					if ($i!=$recipe_id) return "";
					if (!file_exists($root."/recipes_user/".$currentUserId)) mkdir($root."/recipes_user/".$currentUserId);
					mkdir($root."/recipes_user/".$currentUserId."/".$i);
					return array("return"=>$i);
				}
			}
		}
		return "";
	}

	public function getNameCategoryFromOldIdBase($id_element){
		$id_element = is_numeric($id_element) ? $id_element : false;
		if (!$id_element) return "";
		$first = $this->first_id($id_element);

		$hierarchy = umiHierarchy::getInstance();
		$element = $hierarchy -> getElement($first);
		if($element instanceOf umiHierarchyElement){
			$parent_id = $element -> getParentId();
			$parent = $hierarchy -> getElement($parent_id);
			$parent_name = $parent -> h1;
			$parent_link = $parent -> link;
			return array("h1"=>$parent_name,"link"=>$parent_link);
		} else return "";
		return "";
	}

	//Перевод времени
	public function tr_time($getTimeMinutes = 0){
		$day = floor($getTimeMinutes/(60*24));
		$hours = floor(($getTimeMinutes - $day*60*24)/60);
		$minutes = $getTimeMinutes - $day*60*24 - $hours*60;
		$result = (is_numeric($day) and ($day>0)) ? $day." д " : "";
		$result .= (is_numeric($hours) and ($hours>0)) ? $hours." ч " : "";
		$result .= (is_numeric($minutes) and ($minutes>0)) ? $minutes." мин" : "";
		return $result;
	}

	//Счетчик посещаемости
	public function viewsCounter($pageId = false){
		if (!is_numeric($pageId)) return false;
		if(session_id() == '') session_start();

		$id_session=session_id(); $time_start=time(); $time_past=time()-600; //Переменные(id - id сессии, time-текущее время,
		//past-время, после которого надо удалять сессии).
		l_mysql_query("DELETE FROM www_online WHERE last_time < '$time_past'"); //Удаляем старые сессии
		$result_session=mysql_query("SELECT last_time FROM www_online WHERE sess_id='$id_session' AND id='$pageId'"); //Выбираем таблицу
		$session_rows=mysql_num_rows($result_session); //Если в таблице есть sess_id с $id, то равно 1, иначе 0
		if ($session_rows!=0) {
			mysql_query("UPDATE www_online SET last_time='$time_start' WHERE sess_id='$id_session' AND id='$pageId'");
		}
		else {
			mysql_query("INSERT INTO www_online (last_time, sess_id, id) VALUES ('$time_start', '$id_session', '$pageId')");
			$h = umiHierarchy::getInstance();
			//экземпляр страницы
			$element = $h->getElement($pageId);
			$typeId = $element->getObjectTypeId();
			if (($typeId == 66) or ($typeId == 73) or ($typeId == 88)){
				$views = $element->views + 1;
				$element -> setValue('views',$views);
			}
		}
	}

	//Импорт видео из tvigle ========================================================================================================================
	public function import_video(){
		global $tvigle_rubric, $tvigle_video;
		$root = CURRENT_WORKING_DIR;
		$hierarchy = umiHierarchy::getInstance();
		$soap = new SoapClient('http://pub.tvigle.ru/soap/index.php?wsdl',array('login'=>'frolikovigor','password'=>'frolikov191082'));


//		$list_video = $soap->GetVideo(2062);
//		print_r($list_video); die;


		//$rubrics = array(1926,2548,197,1276,161,168,489);
		$general_page = umiHierarchy::getInstance()->getElement(1);
		$get_rubrics = explode(",",$general_page->getValue('tvigle_category'));
		$rubrics = array();
		foreach($get_rubrics as $get_rubric){
			$parse_item = explode(">",$get_rubric);
			$rubrics[$parse_item[1]] = $parse_item[0];
		}

		//Формирование массива с id рубрик в tvigle
		$pages = new selector('pages');
		$pages->types('object-type')->id(76);
		$pages->where('is_active')->equals(array(1,0));
		$result = $pages->result();
		foreach($result as $obj){
			$tvigle_rubric[$obj->id] = $obj->getValue('id_tvigle');
		}

		//Формирование массива с id видео в tvigle
		$pages = new selector('pages');
		$pages->types('object-type')->id(77);
		$pages->where('is_active')->equals(array(1,0));
		$result = $pages->result();
		foreach($result as $obj){
			$tvigle_video[$obj->id] = $obj->getValue('id_tvigle');
		}

		foreach($rubrics as $idParent=>$id_rubric){
			$list_rubric = $soap->GetCatalog($id_rubric);

			if ($list_rubric){

				$list_rubric = $this->object2array($list_rubric);

				//Создание рубрики
				/*
				if (!in_array($list_rubric[0]['id'], $tvigle_rubric)){
					$element_id = $hierarchy->addElement($idParent, 27,$list_rubric[0]['name'] ,'',76);
					$element = $hierarchy->getElement($element_id);
					$element -> setValue('h1',$list_rubric[0]['name']);

					if ($list_rubric[0]['logo']){
						$get_file = file_get_contents($list_rubric[0]['logo']);
						$file_type = substr($list_rubric[0]['logo'],strrpos($list_rubric[0]['logo'],"."),4);
						file_put_contents($root."/images/tvigle/".$list_rubric[0]['id'].$file_type,$get_file);
						$element -> setValue('logo',"./images/tvigle/".$list_rubric[0]['id'].$file_type);
					}

					$element -> setValue('id_tvigle',$list_rubric[0]['id']);
					$element -> setValue('priority',$list_rubric[0]['priority']);
					$element -> setValue('description',$list_rubric[0]['text']);
					$element -> setIsActive(true);
					permissionsCollection::getInstance()->setDefaultPermissions($element_id);
					$element ->commit();
				} else $element_id = array_search($list_rubric[0]['id'],$tvigle_rubric);
				*/
				$element_id = $idParent;

				unset($list_rubric[0]);
				$list_rubric = array_values($list_rubric);
				$parent_id = -1;

				//Перебор остальных строк массива (списка всех подрубрик и видео)
				foreach($list_rubric as $id=>$item){
					if ($parent_id == $list_rubric[$id]['parent']) continue;
					if ($this->verify_item_video($list_rubric, $id)){
						$this->create_rubric_video($list_rubric, $element_id, $id);				//Создание рубрики
						$parent_id = $list_rubric[$id]['id'];
					}
					else{
						$this->create_last_rubric($list_rubric, $element_id, $id);				//Создание страницы видео
					}
				}
			}
		}

		//Удаление из базы видео, которого уже нет в tvigle (оставшиеся элементы массива $tvigle_video)
		foreach ($tvigle_video as $id=>$video){
			$hierarchy->delElement($id);
		}
		return true;
	}

	//Проверки, является элемент рубрикой видео или страницей видео (если у элемента есть дочерние элементы - рубрика)
	public function verify_item_video($list_rubric=false, $id=false){
		$id_parent=$list_rubric[$id]['id'];
		$result=false;
		foreach ($list_rubric as $item){
			if ($item['parent']==$id_parent) {$result = true; break;}
		}
		return $result;
	}

	//Создание рубрики видео
	public function create_rubric_video($list_rubric=false, $id_rubric_h, $id_arr){
		global $tvigle_rubric, $tvigle_video;
		$root = CURRENT_WORKING_DIR;
		$hierarchy = umiHierarchy::getInstance();
		if (!in_array($list_rubric[$id_arr]['id'], $tvigle_rubric)){
			$element_id = $hierarchy->addElement($id_rubric_h, 27 ,$list_rubric[$id_arr]['name'] ,'',76);
			$element = $hierarchy->getElement($element_id);
			if (is_object($element)){
				$element -> setValue('h1',$list_rubric[$id_arr]['name']);
				if ($list_rubric[$id_arr]['logo']){
					$get_file = file_get_contents($list_rubric[$id_arr]['logo']);
					$file_type = substr($list_rubric[$id_arr]['logo'],strrpos($list_rubric[$id_arr]['logo'],"."),4);
					file_put_contents($root."/images/tvigle/".$list_rubric[$id_arr]['id'].$file_type,$get_file);
					$element -> setValue('logo',"./images/tvigle/".$list_rubric[$id_arr]['id'].$file_type);
				}
				$element -> setValue('id_tvigle',$list_rubric[$id_arr]['id']);
				$element -> setValue('priority',$list_rubric[$id_arr]['priority']);
				$element -> setValue('description',$list_rubric[$id_arr]['text']);
				$element -> setIsActive(true);
				permissionsCollection::getInstance()->setDefaultPermissions($element_id);
				$element -> commit();
			}
		} else $element_id = array_search($list_rubric[$id_arr]['id'],$tvigle_rubric);

		$id_parent_tv = $list_rubric[$id_arr]['id'];
		foreach($list_rubric as $id=>$item){
			if ($item['parent']==$id_parent_tv)
				if ($this->verify_item_video($list_rubric, $id))
					$this->create_rubric_video($list_rubric, $element_id, $id);
				else {
					$this->create_last_rubric($list_rubric, $element_id, $id);					//Создание страницы видео
				}
		}

		return true;
	}

	//Создание последней рубрики и страницы видео
	public function create_last_rubric($list_rubric=false, $id_rubric_h, $id_arr){
		global $tvigle_rubric, $tvigle_video;
		$root = CURRENT_WORKING_DIR;
		$soap = new SoapClient('http://pub.tvigle.ru/soap/index.php?wsdl',array('login'=>'womanseek','password'=>'ivemuxodi'));
		$hierarchy = umiHierarchy::getInstance();
		if (!in_array($list_rubric[$id_arr]['id'], $tvigle_rubric)){
			$element_id = $hierarchy->addElement($id_rubric_h, 27 ,$list_rubric[$id_arr]['name'] ,'',76);
			$element = $hierarchy->getElement($element_id);
			if (is_object($element)){
				$element -> setValue('h1',$list_rubric[$id_arr]['name']);
				if ($list_rubric[$id_arr]['logo']){
					$get_file = file_get_contents($list_rubric[$id_arr]['logo']);
					$file_type = substr($list_rubric[$id_arr]['logo'],strrpos($list_rubric[$id_arr]['logo'],"."),4);
					file_put_contents($root."/images/tvigle/".$list_rubric[$id_arr]['id'].$file_type,$get_file);
					$element -> setValue('logo',"./images/tvigle/".$list_rubric[$id_arr]['id'].$file_type);
				}
				$element -> setValue('id_tvigle',$list_rubric[$id_arr]['id']);
				$element -> setValue('priority',$list_rubric[$id_arr]['priority']);
				$element -> setValue('description',$list_rubric[$id_arr]['text']);
				$element -> setIsActive(true);
				permissionsCollection::getInstance()->setDefaultPermissions($element_id);
				$element -> commit();
			}
		} else $element_id = array_search($list_rubric[$id_arr]['id'],$tvigle_rubric);

		//Создание страницы видео
		$id_last_rubric = $list_rubric[$id_arr]['id'];
		$list_video = $soap->GetVideo($id_last_rubric);
		if ($list_video){
			$list_video = $this->object2array($list_video);
			//Перебор всех строк массива (списка видео)
			$parent_id = $element_id;
			foreach($list_video as $id=>$item){
				if (!in_array($item['id'], $tvigle_video)){
					$element_id = $hierarchy->addElement($parent_id, 27 ,$item['name'] ,'',77);
					$element = $hierarchy->getElement($element_id);
					if (is_object($element)){
						$element -> setValue('h1',$item['name']);
						$element -> setValue('id_tvigle',$item['id']);
						$element -> setValue('catalog',$item['catalog']);
						$element -> setValue('tags',$item['tags']);
						$element -> setValue('meta_keywords',$item['tags']);
						$element -> setValue('duration',$item['duration']);
						$element -> setValue('geo',$item['geo']);
						$element -> setValue('date',$item['date']);
						$element -> setValue('swf',$item['swf']);
						$element -> setValue('frame',$item['frame']);
						$element -> setValue('rs',$item['rs']);
						$element -> setValue('code',$item['code']);
						$element -> setValue('under',$item['under']);
						$element -> setValue('subtitles',$item['subtitles']);
						$element -> setValue('mob',$item['mob']);

						if ($item['img']){
							$get_file = file_get_contents($item['img']);
							$file_type = substr($item['img'],strrpos($item['img'],"."),4);
							file_put_contents($root."/images/tvigle/".$item['id'].$file_type,$get_file);
							$this->crop($root."/images/tvigle/".$item['id'].$file_type, $root."/images/tvigle/".$item['id']."_300".$file_type,'3:2');
							$this->resize($root."/images/tvigle/".$item['id']."_300".$file_type, $root."/images/tvigle/".$item['id']."_300".$file_type,300,200);
							$element -> setValue('img',"./images/tvigle/".$item['id'].$file_type);
						}
						$element -> setIsActive(true);
						permissionsCollection::getInstance()->setDefaultPermissions($element_id);

						$old_mode = umiObjectProperty::$IGNORE_FILTER_INPUT_STRING;		//Откючение html сущн.
						umiObjectProperty::$IGNORE_FILTER_INPUT_STRING = true;
						$element -> setValue('anons',$item['anons']);
						$element -> commit();
						umiObjectProperty::$IGNORE_FILTER_INPUT_STRING = $old_mode;		//Вкл. html сущн.
					}
				} else	{
					$id_search = array_search($item['id'], $tvigle_video);
					unset($tvigle_video[$id_search]); 	//Удаление элементов, которые есть в базе
				}
			}
			//==========================================
		}

		return true;
	}

	public function object2array($object)
	{
		if (is_array($object) || is_object($object)) {
			$result = array();
			foreach ($object as $key => $value) {
				$result[$key] = $this->object2array($value);
			}
			return $result;
		}
		return $object;
	}
	//===============================================================================================================================================

	//Список всех видеороликов
	public function list_video($id_parent=3){
		$p = (int) getRequest('p');
		if ($p<0) $p=0;
		$num_on_page = umiHierarchy::getInstance()->getElement(1)->getValue('video_per_page');
		$pages = new selector('pages');
		$pages->types('object-type')->id(77);
		$pages->where('hierarchy')->page($id_parent)->childs(3);
		$pages->limit($p*$num_on_page,$num_on_page);
		$result = $pages->result();

		$sum = array();
		foreach($result as $obj){
			$arr = array();
			$arr['attribute:id'] = $obj->id;
			$arr['attribute:name'] = $obj->h1;
			$arr['attribute:link'] = $obj->link;
			$arr['attribute:img'] = $obj->img;
			$arr['anons'] = $obj->anons;
			$arr['anons_cut'] = $this->truncate_words($obj->anons,200);

			$sum[]=$arr;
		}
		$result = array("nodes:item"=>$sum, "rubric_id"=>$id_parent, "per_page"=>$num_on_page, "total"=>$pages->length);
		return $result;
	}

	//Список всех рубрик видео
	public function list_rubric_video($id_parent=3){
		$pages = new selector('pages');
		$pages->types('object-type')->id(76);
		$pages->where('hierarchy')->page($id_parent)->childs(1);
		$result = $pages->result();

		$sum = array();
		foreach($result as $obj){
			$arr = array();
			$arr['attribute:id'] = $obj->id;
			$arr['attribute:name'] = $obj->h1;
			$arr['attribute:link'] = $obj->link;
			$arr['attribute:ico'] = $obj->ico;
			$sum[]=$arr;
		}
		$result = array("nodes:item"=>$sum);
		return $result;
	}

	//Получение id баннера
	public function getRandBanner($type_id = 66){
		$root = CURRENT_WORKING_DIR;
		$hierarchy = umiHierarchy::getInstance();
		$pages = new selector('pages');
		$pages -> types('object-type')->id($type_id);
		$pages -> where('banner')->equals(true);
		$pages -> order('rand');
		$pages -> limit(0,1);
		$pages = $pages -> result();
		$element = $hierarchy->getElement($this->first_id($pages[0]->id));
		if ($pages[0]->id_old_base)	return array("id"=>$element->id, "old_id"=>$element->id_old_base, "h1"=>$element->h1, "link"=>$element->link);
		else 						return array("id"=>$element->id, "h1"=>$element->h1, "link"=>$element->link);
	}

    //Получение id баннера Larimel
    public function getRandBannerLarimel(){
        $root = CURRENT_WORKING_DIR;
        $hierarchy = umiHierarchy::getInstance();
        $pages = new selector('pages');
        $pages -> types('object-type')->id(88);
        if (rand(0,1) == 0) $pages -> where('recomended_larimel')->equals(true);
        $pages -> where('img_1_larimel')->isnotnull();
        $pages -> order('rand');
        $pages -> limit(0,10);
        $pages = $pages -> result();
        $result = array();
        foreach($pages as $page){
            $getPape = $hierarchy->getElement($page->id);
            if($getPape instanceOf umiHierarchyElement){
                $image = $getPape->img_1_larimel;
                $url_img = trim($image->getFilePath(),".");
                $size = getimagesize("http://".$_SERVER['SERVER_NAME'].$url_img);
                $result[$page->id] = $size[0]/$size[1];
            }
        }
        asort($result);
        $id = key($result);
        $element = $hierarchy->getElement($id);
        $image = $element->img_1_larimel;
        $url_img = "http://".$_SERVER['SERVER_NAME'].trim($image->getFilePath(),".");
        $h1 = $element->h1;
        if ($element->recomended_larimel) return array("id"=>$id,"img"=>$url_img,"h1"=>$h1,"link"=>$element->link,"description_larimel"=>$element->description_larimel,"recomended"=>"1");
        else return array("id"=>$id,"img"=>$url_img,"h1"=>$h1,"link"=>$element->link,"description_larimel"=>$element->description_larimel);
    }

    //Выбор случайных товаров Larimel
    public function getRandLarimelElements($limit = 10){
        $pages = new selector('pages');
        $pages -> types('object-type')->id(88);
        $pages -> where('img_1_larimel')->isnotnull();
        $pages -> order('rand');
        $pages -> limit(0,$limit);
        $pages = $pages -> result();
        $result = array();
        foreach($pages as $page){
            $arr = array();
            $arr['attribute:id'] = $page->id;
            $result[] = $arr;
        }
        return array("nodes:item"=>$result);
    }


	//Парсинг текста рецепта или статьи (определение блоков-ссылок на рецепты и рекламы)
	public function parseTextRecipe($id = false){
		if ($id){
			$hierarchy = umiHierarchy::getInstance();
			$content = $hierarchy->getElement($id)->content;
			//Определение ссылок на рецепты (формат - {recipe=...})
			while (strpos($content,"{recipe=")){
				$content_st = strpos($content,"{recipe");
				$content_f  = strpos($content,"}",$content_st)+1;
				$id_recipe = substr($content,$content_st, $content_f-$content_st);
				$id_recipe = (int) strtr($id_recipe,array("{recipe="=>"", "}"=>""));
				//$result_transform = file_get_contents('udata://content/getElementOfRecipe/'.$id_recipe.'/?transform=/modules/content/article_view.xsl');
				$content = substr_replace($content,"",$content_st,$content_f-$content_st);
			}
			return $content;
		}
		return true;
	}

	//Список картинок-баннеров для главной страницы
	public function listWideBanner($num = 3, $type_id = 66, $parent_id = false, $path="/recipes/", $description_field = "meta_descriptions"){
		$h = umiHierarchy::getInstance();
        $root = CURRENT_WORKING_DIR;;
		if (strpos($type_id,",") === false){
			$pages_ = new selector('pages');
			$pages_ -> types('object-type')->id($type_id);
			if ($parent_id) $pages_ -> where('hierarchy')->page($parent_id)->childs(3);
			$pages_ -> where('pos')->isnotnull(false);
			$pages_ -> order('rand');
			$pages_ -> limit(0,$num*2);
			$pages = $pages_ -> result();
		} else {
			$pages = array();
			$type_id = explode(",",$type_id);
			foreach($type_id as $type_id_item){
				$pages_ = new selector('pages');
				$pages_ -> types('object-type')->id($type_id_item);
				if ($parent_id) $pages_ -> where('hierarchy')->page($parent_id)->childs(3);
				$pages_ -> where('pos')->isnotnull(false);
				$pages_ -> order('rand');
				$pages_ -> limit(0,$num*2);
				$pages_ = $pages_->result();
				$pages = array_merge($pages, $pages_);
			}
		}
		$arr = array();
		$unique = array();
		$i = 0;
		$pages = $this->shuffle_assoc($pages);
		foreach($pages as $banner){
			$arr_ = array();
			if (in_array($this->first_id($banner -> id), $unique)) continue; else $unique[] = $this->first_id($banner -> id);
			$i++;
			$element = $h -> getElement($this->first_id($banner -> id), true);
			$arr_['attribute:id'] = $element -> id;
			$arr_['name'] = $element -> h1;

			if ($element->id_old_base) $arr_['attribute:link']  = $path.$element->id_old_base."/"; else $arr_['attribute:link'] = $element -> link;

			$arr_['description'] = $element -> $description_field;
			if ($element->id_old_base) $arr_['attribute:img'] = $path.$element->id_old_base."/wide_banner.jpg"; else $arr_['attribute:img'] = $path.$this->first_id($element->id)."/wide_banner.jpg";
			$arr_['attribute:pos'] = $element -> pos;

            //Проверка, есть ли в наличии изображение
            if (!file_exists($root.$arr_['attribute:img'])) continue;
			$arr[] = $arr_;
			if ($i == $num) break;
		}
		return array("nodes:item" => $arr);
	}

	function shuffle_assoc($list) {
		if (!is_array($list)) return $list;

		$keys = array_keys($list);
		shuffle($keys);
		$random = array();
		foreach ($keys as $key) {
			$random[$key] = $list[$key];
		}
		return $random;
	}

	//Пересчет всех рецептов (общее время приготовления, цена блюда, калорийность)
	public function recipes_recount($recipe_id_rec = false){
		ini_set('memory_limit', '1000M');
		ini_set('max_execution_time', 200);
		$hierarchy = umiHierarchy::getInstance();
		$recipes = new selector('pages');
		$recipes -> types('object-type')->id(66);
		$exist = array();
		$max_rating = 0;
		foreach($recipes as $recipe){
			if ($recipe_id_rec) $recipe_id = $this->first_id($recipe_id_rec);
			if (in_array($recipe_id, $exist)) continue; else $exist[] = $recipe_id;
			$get_recipe = $hierarchy -> getElement($recipe_id);
			$pages = new selector('objects');
			$pages -> types('object-type')->id(70);
			$pages -> where('rid')->equals($recipe_id);
			$pages_res = $pages->result();
			$price = 0;
			$calories = 0;
			$weight = 0;
			foreach($pages_res as $page){
				$product = $hierarchy -> getElement($page->pid, true);
				$price += $product->price * $page->amount;
				$weight += 100 * $page->amount;
				if ($page->caloric_in_ready_recipe){
					$calories += $product->caloric * $page->amount * $page->koef_in_recipe/100;
				} else {
					$calories += $product->caloric * $page->amount;
				}
			}
			$get_recipe -> setValue('price',round($price,2));
			$get_recipe -> setValue('total_time',$get_recipe->prep_time + $get_recipe->cooking_time);
			$get_recipe -> setValue('weight',$weight);
			$get_recipe -> setValue('weight_portion',round($weight / $get_recipe->num_servings));
			$get_recipe -> setValue('calories',$calories);
			$get_recipe -> setValue('calories_portion',round($calories / $get_recipe->num_servings));
			$get_recipe -> setValue('calories_sto',round(100 * $calories / $weight));
			$get_recipe -> commit();
			if ($recipe_id_rec) return "";
			$rating = $get_recipe->rating;
			if ($rating > $max_rating) $max_rating = $rating;
		}
		$gen_page = $hierarchy -> getElement(1);
		$gen_page -> setValue('max_rating', $max_rating);
		$gen_page -> commit();
		return "";
	}

	//Формирование релевантных рецептов к рецептам
	public function form_recipes_relevant($id_element = false){
		$hierarchy = umiHierarchy::getInstance();
		$general_page = $hierarchy->getElement(1);
		$parametrs = unserialize($general_page->getValue('parametrs'));
		$last_recipe = false;
		if (isset($parametrs['last_recipe'])) $last_recipe = $parametrs['last_recipe'];
		if (!$last_recipe) $last_recipe = 0;

        $last_recipe = $id_element ? $id_element : $last_recipe;

        $cover = 3;
        if ($id_element) $cover = 1;

        for($cicle = 0; $cicle < $cover; $cicle++){
			$recipes = new selector('pages');
			$recipes -> types('object-type')->id(66);
            if ($id_element) $recipes -> where('id') -> equals($last_recipe);
            else $recipes -> where('id') -> more($last_recipe);
			$recipes -> limit(0,1);
			if (!$recipes->length){
				$recipes = new selector('pages');
				$recipes -> types('object-type')->id(66);
				$recipes -> where('id') -> more(0);
			}
			if ($recipes->length){
				$recipe = reset($recipes->result());
				$recipe_id = $recipe -> id;
			}
			unset($recipes);
			$last_recipe = $recipe_id;

			$recipe_id = $this->first_id($recipe_id);

			$recipes_copies_list = $this->first_id($recipe_id, true);
			$parents_list = array();
			foreach($recipes_copies_list as $recipes_copies){
				$parent_recipe = $hierarchy -> getElement($recipes_copies);
				if($parent_recipe instanceOf umiHierarchyElement){
					$parent_recipe = $parent_recipe	-> getParentId();
					if ($parent_recipe != 2) $parents_list[] = $parent_recipe;
				}
			}
			//Математическая модель=============================================
			//Определяем категорию продуктов (for_select)
			$items = new selector('objects');
			$items -> types('object-type')->id(70);
			$items -> where('rid')->equals($recipe_id);
			$items -> order('npp');
			$products = $items -> result();
			$for_select = array();
			foreach($products as $product){
				$get_product = $hierarchy->getElement($product->pid, true);
				$for_select[] = $get_product->for_select;
			}
			unset($items);
			//Выбираем все рецепты с этими категориями продуктов
			$recipes_relevant = array();
			foreach($for_select as $i=>$product){
				//Перебор всех продуктов
				$sel_product = new selector('pages');
				$sel_product -> types('object-type')->id(67);
				$sel_product -> where('for_select')->equals($product);
				$sel_product_res = $sel_product->result();
				unset($sel_product);
				foreach($sel_product_res as $sel_product_res_){
					$items = new selector('objects');
					$items -> types('object-type')->id(70);
					$items -> where('pid')->equals($sel_product_res_);
					$res_items = $items->result();
					unset($items);
					foreach($res_items as $item){
						$get_recipe_of_product = $hierarchy -> getElement($item->rid);
                        if ($get_recipe_of_product instanceof umiHierarchyElement){
                            if (!$get_recipe_of_product -> getIsActive()) continue;
                            $getWeight_recipe_of_product = $get_recipe_of_product -> weight;
                            $koef = 1;
                            if (in_array($get_recipe_of_product -> getParentId(),$parents_list)) $koef = 100;
                            if ($getWeight_recipe_of_product > 0) {} else $getWeight_recipe_of_product = 1;
                            if (isset($recipes_relevant[$item->rid])) $recipes_relevant[$item->rid] += ($koef * ($item->amount * $item->amount / $getWeight_recipe_of_product) * $item->koef_in_recipe / ($i+1)) / ($item->npp+1);
                            else  $recipes_relevant[$item->rid] = ($koef * ($item->amount * $item->amount / $getWeight_recipe_of_product) * $item->koef_in_recipe / ($i+1)) / ($item->npp+1);
                        }
					}
				}

			}
			unset($items);
			if (isset($recipes_relevant[$recipe_id])) unset($recipes_relevant[$recipe_id]);
			arsort($recipes_relevant);
			$recipes_relevant = array_slice($recipes_relevant, 0, 36, TRUE);
			$recipes_relevant = array_keys($recipes_relevant);
			$element = $hierarchy->getElement($recipe_id);
			if($element instanceOf umiHierarchyElement) {
				$element -> setValue('relevant_recipes', serialize($recipes_relevant));
				$element -> commit();
			}
		}

		//Сохранение параметров
		$parametrs['last_recipe'] = $last_recipe;
		$general_page -> setValue('parametrs',serialize($parametrs)); $general_page -> commit();
		return $recipe_id;
	}

	//Список релевантных рецетов
	public function list_relevant_recipes($recipe_id = false, $limit = false){
		$recipe_id = is_numeric($recipe_id) ? $recipe_id : false;
		if (!$recipe_id) return true;
		$recipe_id = $this->first_id($recipe_id);
		$hierarchy = umiHierarchy::getInstance();
		$element = $hierarchy->getElement($recipe_id);
		$result = array();
		if($element instanceOf umiHierarchyElement) {
			$list = unserialize($element -> relevant_recipes);
			if ($list){
				if ($limit) $list = array_slice($list,0,$limit);
				foreach($list as $item){
					$res['attribute:id'] = $item;
					$get_recipe = $hierarchy->getElement($item);
					if($get_recipe instanceOf umiHierarchyElement){
						$res['attribute:link'] = $get_recipe->link;
						$res['attribute:old_id'] = $get_recipe->id_old_base;
						$res['name'] = $get_recipe->h1;
						$res['descriptions'] = $get_recipe->meta_descriptions;
					}
					$result[] = $res;
				}
			} else return "";
		}
		return array("nodes:item"=>$result);
	}

	//Формирование релевантных рецептов к ингредиентам
	public function form_recipes_relevant_toIngredient($ingredient_id = false, $limit = 30){
		$hierarchy = umiHierarchy::getInstance();
		//Математическая модель=============================================
		//Определяем категорию продуктов (for_select)
		$for_select = $hierarchy->getElement($ingredient_id, true)->for_select;

		//Перебор всех продуктов
		$sel_product = new selector('pages');
		$sel_product -> types('object-type')->id(67);
		$sel_product -> where('for_select')->equals($for_select);
		$sel_product_res = $sel_product->result();
		unset($sel_product);
		$ingredients_relevant = array();
		foreach($sel_product_res as $sel_product_res_){
			$items = new selector('objects');
			$items -> types('object-type')->id(70);
			$items -> where('pid')->equals($sel_product_res_);
			$res_items = $items->result();
			unset($items);
			foreach($res_items as $item){
				if (isset($ingredients_relevant [$item->rid])) $ingredients_relevant [$item->rid] += ($item->amount * $item->koef_in_recipe / ($item->npp+1)) / ($item->npp+1);
				else  $ingredients_relevant [$item->rid] = ($item->amount * $item->koef_in_recipe / ($item->npp+1)) / ($item->npp+1);
			}
		}
		unset($items);
		if (isset($ingredients_relevant [$ingredient_id])) unset($ingredients_relevant [$ingredient_id]);
		arsort($ingredients_relevant );
		$ingredients_relevant  = array_slice($ingredients_relevant , 0, $limit, TRUE);
		$ingredients_relevant  = array_keys($ingredients_relevant);
		$result = array();
		foreach($ingredients_relevant as $ingredient){
			$res = array();
			$res['attribute:id'] = $ingredient;
			$get_recipe = $hierarchy->getElement($ingredient);
			if($get_recipe instanceOf umiHierarchyElement){
				$res['attribute:link'] = $get_recipe->link;
				$res['attribute:old_id'] = $get_recipe->id_old_base;
				$res['name'] = $get_recipe->h1;
				$res['descriptions'] = $get_recipe->meta_descriptions;
			}
			$result[] = $res;
		}

		return array("nodes:item"=>$result);
	}



	//Формирование списка популярных ингредиентов
	public function form_ingredients_popular($generate = false, $amount = 30){
		$hierarchy = umiHierarchy::getInstance();
		if (!$generate){
			$result = unserialize($hierarchy->getElement(22)->top_ingredients);
			$result = $result['nodes:item'];
			$result = array_slice($result, 0, $amount, true);
			$result = array("nodes:item"=>$result);
			return $result;
		} else {
			$products = array();
			$items = new selector('objects');
			$items -> types('object-type')->id(70);
			$items = $items -> result();
			foreach($items as $item){
				$element = $hierarchy->getElement($item->pid);
				if ($element->no_show_popular_ingredient) continue;
				if (isset($products[$element->for_select]))
					$products[$element->for_select] += $item->amount * $item->koef_in_recipe / ($item->npp+1);
				else
					$products[$element->for_select] = $item->amount * $item->koef_in_recipe / ($item->npp+1);
			}
			arsort($products);
			reset($products);
			$products = array_slice($products,0,100, TRUE);
			$result = array();
			foreach($products as $id=>$product){
				$sel_product = new selector('pages');
				$sel_product -> types('object-type')->id(67);
				$sel_product -> where('for_select')->equals($id);
				$sel_product -> limit(0,1);
				$sel_product_res = $sel_product->result();
				$id_first_product  = $sel_product_res[0]->id;
				$element = $hierarchy -> getElement($id_first_product);
				if($element instanceOf umiHierarchyElement){
					$res_ = array();
					$res_['attribute:id'] = $id_first_product;
					$res_['attribute:name'] = umiObjectsCollection::getInstance()->getObject($element->for_select)->getName();
					$res_['attribute:link'] = $element->link;
					$result[] = $res_;
				}
			}
			$hierarchy->getElement(22)->setValue('top_ingredients', serialize(array("nodes:item"=>$result)));
		}
		return "";
	}

	//Список валют
	public function listCurrency($id_currency = false, $console = false){
		$items = new selector('objects');
		$items -> types('object-type')->id(79);
		$items = $items -> result();
		if ($id_currency) {
			foreach($items as $item){
				if ($item->id == $id_currency){
					if(session_id() == '') session_start();
					$_SESSION['id_currency'] = $id_currency;
				}
			}
		}

		if (isset($_SESSION['id_currency'])) $id_currency = $_SESSION['id_currency']; else $id_currency = false;
		$result = array();
		$result_console = array();
		foreach($items as $item){
			$res = array();
			$res_console_ = array();

			$res_console_['id'] = $item->id;
			$res['attribute:id'] = $item->id;

			$res_console_['name'] = $item->name;
			$res['attribute:name'] = $item->name;

			$res_console_['exchange'] = $item->exchange;
			$res['attribute:exchange'] = $item->exchange;

			$res_console_['shot'] = $item->shot;
			$res['attribute:shot'] = $item->shot;

			$res_console_['active'] = $item->active;
			$res['attribute:active'] = $item->active;

			if ($id_currency){
				if ($id_currency == $item->id){
					$res_console_['default'] = 1;
					$res['attribute:default'] = 1;
				}
			}
			else{
				$res_console_['default'] = $item->default;
				$res['attribute:default'] = $item->default;
			}
			$result_console[] = $res_console_;
			$result[] = $res;
		}
		if ($console) return ($result_console);
		else return array("nodes:item"=>$result);
	}

	//Список категорий продуктов и продуктов
	public function list_ingredients($parent_id = 22){
		$hierarchy = umiHierarchy::getInstance();
		$element = $hierarchy -> getElement($parent_id);

		if ($parent_id != 22)
		if ($element instanceof umiHierarchyElement) {
			$list_products = unserialize($element -> list_products);
			if (isset($list_products['last_update'])){
				$last_update = $list_products['last_update'];
				unset($list_products['last_update']);
				if ((time() - $last_update) < 3600*24*7) if ($list_products) return array("nodes:item" => $list_products);
			}
		}

		$pages = new selector('pages');
		$pages->where('hierarchy')->page($parent_id)->childs(1);
		$result = $pages -> result();
		$res = array();
		$exist = array();
		foreach($result as $page){
			$type_id = $page->getObjectTypeId();
			$name = "";
			if ($type_id == 68) $name = $page -> h1;
			if ($type_id == 67) {$name = umiObjectsCollection::getInstance()->getObject($page -> for_select); if (is_object($name)) $name = $name -> getName();}
			if (in_array($name,$exist)) continue;
			$res_ = array();
			$res_['attribute:id'] = $page -> id;
			$exist[] = $name;
			$res_['attribute:link'] = $page -> link;
			$res_['name'] = $name;
			$res[] = $res_;
		}
		$res['last_update'] = time();
		if ($parent_id != 22) {
			if ($element instanceof umiHierarchyElement) {
				$element -> setValue('list_products',serialize($res));
			}
		}
		if (isset($res['last_update'])) unset($res['last_update']);
		return array("nodes:item" => $res);
	}

	public function getSocialLikes(){
		$hierarchy = umiHierarchy::getInstance();
		$general_page = $hierarchy->getElement(1);
		$parametrs = unserialize($general_page->getValue('parametrs'));
		$last_element = false;
		if (isset($parametrs['last_element_get_likes'])) $last_element = $parametrs['last_element_get_likes'];
		if (!$last_element) $last_element = 0;

		$elements = array();
		$items = new selector('pages');
		$items->types('object-type')->id(66);
		$result = $items->result();
		foreach($result as $item) $elements[$this->first_id($item->id)] = "";

		$items = new selector('pages');
		$items->types('object-type')->id(73);
		$result = $items->result();
		foreach($result as $item) $elements[$this->first_id($item->id)] = "";
		$elements = array_keys($elements);
		$domain = $_SERVER['SERVER_NAME'];
		$domain = "gotovimvse.com";
		for($i=0; $i<2; $i++){
			$get_current_element = $hierarchy -> getElement($elements[$last_element]);
			$rating = 0;
			if($get_current_element instanceOf umiHierarchyElement){
				$id_old_base = $get_current_element->id_old_base;
				$vk_id_in_group = $get_current_element->vk_id_in_group;
				if ($id_old_base){
					//$rating += $this->getMailLikes("http://".$domain."/recipes/".$id_old_base."/");
					$rating += $this->getOdnoklLikes("http://".$domain."/recipes/".$id_old_base."/");
					$rating += $this->getLikesFB("http://".$domain."/recipes/".$id_old_base."/");
					$rating += $this->getShareFB("http://".$domain."/recipes/".$id_old_base."/") * 3;
					$rating += $this->getLikesTwitter("http://".$domain."/recipes/".$id_old_base."/");
					$rating += $this->getLikeVkPage("http://".$domain."/recipes/".$id_old_base."/");
					if ($vk_id_in_group) $rating += $this->getLikeVkWall($vk_id_in_group);
					if ($vk_id_in_group) $rating += $this->getVkWallShares($vk_id_in_group) * 3;
				}
				$get_current_element -> setValue("rating", $rating);
				$get_current_element -> setValue("likes", $rating);
				$get_current_element -> commit();
			}
			$last_element++;
			if (!isset($elements[$last_element])) $last_element = 0;
		}

		//Сохранение параметров
		$parametrs['last_element_get_likes'] = $last_element;
		$general_page -> setValue('parametrs',serialize($parametrs)); $general_page -> commit();
		return "";
	}

	public function getMailLikes($url) {
		$curl = curl_init();
		$url_ = 'http://connect.mail.ru/share_count?url_list='.$url;
		curl_setopt($curl, CURLOPT_URL, $url_);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_results = curl_exec ($curl);
		curl_close($curl);
		$res = json_decode($curl_results, true);
		return $res[$url]['shares'];
	}

	public function getOdnoklLikes($url) {
		$curl = curl_init();
		$url_ = 'http://www.odnoklassniki.ru/dk?st.cmd=shareData&ref='.$url.'&cb=mailru.share.ok.init';
		curl_setopt($curl, CURLOPT_URL, $url_);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_results = curl_exec ($curl);
		curl_close($curl);
		$curl_results=substr($curl_results,strpos($curl_results,"{"),strrpos($curl_results,"}")-strpos($curl_results,"{")+1);
		$res = json_decode($curl_results, true);
		return $res['count'];
	}

	public function getLikesFB($url) {
		$curl = curl_init();
		$url = 'http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls='.$url;
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_results = curl_exec ($curl);
		curl_close($curl);
		$res = json_decode($curl_results);
		return $res[0]->like_count;
	}

	public function getShareFB($url) {
		$curl = curl_init();
		$url = 'http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls='.$url;
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_results = curl_exec ($curl);
		curl_close($curl);
		$res = json_decode($curl_results);
		return $res[0]->share_count;
	}

	public function getLikesTwitter($url) {
		$curl = curl_init();
		$url = 'http://urls.api.twitter.com/1/urls/count.json?url='.$url;
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_results = curl_exec ($curl);
		curl_close($curl);
		$res = json_decode($curl_results);
		return intval( $res->count );
	}

	public function getLikeVkPage($url){
		$vk_page = file_get_contents("https://api.vk.com/method/likes.getList?type=sitepage&owner_id=2903586&page_url=".$url);
		$vk_page_res = json_decode($vk_page, true);
		$vk_page_likes=$vk_page_res['response']['count'];
		return $vk_page_likes;
	}

	public function getLikeVkWall($item_id){
		$vk_wall = file_get_contents('https://api.vk.com/method/likes.getList?type=post&owner_id=-38798757&item_id='.$item_id);
		$vk_wall_res = json_decode($vk_wall, true);
		$vk_wall_likes=$vk_wall_res['response']['count'];
		return $vk_wall_likes;
	}

	public function getVkWallShares($item_id){
		$vk_wall_shares = file_get_contents('https://api.vk.com/method/likes.getList?type=post&owner_id=-38798757&filter=copies&item_id='.$item_id);
		$vk_wall_res_shares = json_decode($vk_wall_shares, true);
		$vk_wall_shares=$vk_wall_res_shares['response']['count'];
		return $vk_wall_shares;
	}

	public function listRecipeImages($id_recipe = false, $service = false, $console = false, $do = false, $img_name = false, $path = "/recipes/"){
		$id_recipe = is_numeric($id_recipe) ? $id_recipe : false;
		if (!$id_recipe) return "";
		$id_recipe = $this->first_id($id_recipe);
		$hierarchy = umiHierarchy::getInstance();
		$recipe = $hierarchy -> getElement($id_recipe);
		$id_old_base = $recipe->id_old_base;
		$str = "";
		$str_service = "";
		$root = CURRENT_WORKING_DIR;;
		if ($id_old_base){
			$id_old_base = is_numeric($id_old_base) ? $id_old_base : false;
			if (!$id_old_base) return "";
			$dir = $root.$path.$id_old_base;
			$id_recipe_ = $id_old_base;
		} else {
			$dir = $root.$path.$id_recipe;
			$id_recipe_ = $id_recipe;
		}

		if ($do){
			if ($do=="del_img"){
				if (file_exists($dir."/".$img_name)) unlink($dir."/".$img_name);
			}
			return "";
		}

		// Открыть существующий каталог и начать считывать его содержимое
		if (is_dir($dir)) {
			$files_=scandir($dir);
			foreach ($files_ as $file){
				if (($file==".") or ($file=="..")) continue;
				if ((strpos(strtolower($file),".jpg")) or (strpos(strtolower($file),".png")) or (strpos(strtolower($file),".gif"))) {
					$str .= "<div class='admin_img_recipe'><img src='".$path.$id_recipe_."/".$file."?".time()."' /><span><img class='del_img_recipe' umi_id='".$id_recipe_."' file_name='".$file."' path='".$path."' src='/images/cms/admin/mac/tree/ico_del.png' />".$file."</span></div>";
				}
				if ($file=="img_600.jpg") $str_service .= "<img width='160' src='".$path.$id_recipe_."/".$file."?".time()."' />";
				if ($file=="banner.jpg") $str_service .= "<img width='76' src='".$path.$id_recipe_."/".$file."?".time()."' />";
				if ($file=="wide_banner.jpg") $str_service = "<img width='241' src='".$path.$id_recipe_."/".$file."?".time()."' />".$str_service;

			}
		}
		if ($console){
			if (!$service) {echo $str; die;} else {echo $str_service; die;}
		} else {
			if (!$service) return $str; else return $str_service;
		}
	}

	//Определение пути фонового изображения (по дереву)
	public function getBackGroundImg($pageId = false){
		$hierarchy = umiHierarchy::getInstance();
		$pageId = $this -> first_id($pageId);
		$getParents = $hierarchy->getAllParents($pageId,true);
		unset($getParents[0]);
		$getParents = array_values($getParents);
		$getParents = array_reverse($getParents);
		foreach($getParents as $parent){
			$getParent = $hierarchy -> getElement($parent);
			if ($getParent->menu_pic_ua){
				$urlImg = $getParent->menu_pic_ua->getFilePath();
				$urlImg = trim($urlImg, ".");
				$root = CURRENT_WORKING_DIR;
				if (!file_exists($root.str_replace(".jpg","_.jpg",$urlImg))){
					$this->crop($root.$urlImg,$root.str_replace(".jpg","_.jpg",$urlImg),'last_lines');
				}
				$bgImg = str_replace(".jpg","_.jpg",$urlImg);
				return array("img" => $urlImg, "bg" => $bgImg);
			}
		}
		return "";
	}

	//Определение пути к файлу css для отдельных страниц
	public function getCssFile($pageId = false){
        $root = CURRENT_WORKING_DIR;
		if (!isset($_SESSION['bg_id'])) $_SESSION['bg_id'] = $this -> first_id($pageId);
		$hierarchy = umiHierarchy::getInstance();
		$pageId = $this ->receipt_prods($pageId);
		$getParents = $hierarchy->getAllParents($pageId,true);
		unset($getParents[0]);
		$getParents = array_values($getParents);
		$getParents = array_reverse($getParents);
		foreach($getParents as $parent){
			$getParent = $hierarchy -> getElement($parent);
			if ($getParent->style_css){
				$urlCss = $getParent->style_css->getFilePath();
				$path_arr = explode("/",$urlCss);
				array_pop($path_arr);
				$category = trim(implode($path_arr,"/"),".")."/";
				$urlCss = trim($urlCss, ".");
                if (file_exists($root.$category."image.jpg")) $exist_currentImg = 1; else $exist_currentImg = 0;
				if ($this -> first_id($_SESSION['bg_id']) == $pageId) return array("css" => $urlCss, "category"=>$category, "existcurrentimage"=>$exist_currentImg);
				else {
					//Определение адреса предыдущей страницы
					$pageId_prev = $this -> first_id($_SESSION['bg_id']);
					$getParents = $hierarchy->getAllParents($pageId_prev,true);
					unset($getParents[0]);
					$getParents = array_values($getParents);
					$getParents = array_reverse($getParents);
					foreach($getParents as $parent){
						$getParent = $hierarchy -> getElement($parent);
						if ($getParent->style_css){
							$urlCss_prev = $getParent->style_css->getFilePath();
							$path_arr_prev = explode("/",$urlCss_prev);
							array_pop($path_arr_prev);
                            $category_prev = trim(implode($path_arr_prev,"/"),".")."/";
                            if (file_exists($root.$category_prev."image.jpg")) $exist_prevImg = 1; else $exist_prevImg = 0;
							if ($category != $category_prev) {
								$_SESSION['bg_id'] = $pageId;
								return array("css" => $urlCss, "category"=>$category, "previous"=>$category_prev, "existcurrentimage"=>$exist_currentImg, "existpreviousimage"=>$exist_prevImg);
							}
							else return array("css" => $urlCss, "category"=>$category, "existcurrentimage"=>$exist_currentImg, "existpreviousimage"=>$exist_prevImg);
						}
					}
				}
			}
		}
		return "";
	}

    public function getLarimelElement($id = false){
        $root = CURRENT_WORKING_DIR;
        $hierarchy = umiHierarchy::getInstance();
        $oC = umiObjectsCollection::getInstance();
        $element = $hierarchy->getElement($id);
        $prices_import = cmsController::getInstance()->getModule("prices_import");
        $fields = $prices_import->get_fields_type(88, true);
        unset($fields[0]);
        $arr = array();
        foreach ($fields as $field){
            $value = $element->$field['name'];
            if (is_array($value)){
                //Если варианты покупки, сортируем варианты по цене от большей к меньшей
                if ($field['name'] == 'variants_larimel'){
                    foreach($value as $i=>$val_item){
                        $value[$i] = array_reverse($value[$i], true);
                        $value[$i]['attribute:title'] = $oC -> getObject($val_item['rel'])->getName();
                    }
                    rsort($value);
                    $arr[$field['name']] = array("nodes:item"=>$value);
                    continue;
                }
                //В остальных случаях
                $arr[$field['name']] = array("nodes:item"=>$value);
                continue;
            }

            //Если изображения
            if (strpos($field['name'],"mg_") and strpos($field['name'],"larimel")){
                if ($value){
                    if (filesize($root.$value) > 1000) $arr[$field['name']] = trim($value->getFilePath(),"."); else continue;
                    $file_img_name = reset(explode(".",end(explode("/",$value))));
                    $file_img_type = end(explode(".",end(explode("/",$value))));
                    if (!file_exists($root."/images/larimel/".$id."/".$file_img_name."_300.".$file_img_type)) {
                        $this->resize($root."/images/larimel/".$id."/".$file_img_name.".".$file_img_type, $root."/images/larimel/".$id."/".$file_img_name."_300.".$file_img_type, 300, false, $percent = false);
                    }
                    if (!file_exists($root."/images/larimel/".$id."/".$file_img_name."__250.jpg")) {
                        $this->resize($root."/images/larimel/".$id."/".$file_img_name.".".$file_img_type, $root."/images/larimel/".$id."/".$file_img_name."__250.jpg", false, 250, $percent = false);
                    }
                    $arr[$field['name']."_300"] = "/images/larimel/".$id."/".$file_img_name."_300.".$file_img_type;
                }
                continue;
            }

            //Если текст
            if ($field['name']=='description_larimel'){
                $arr[$field['name']] = $value;
                if (strpos($arr[$field['name']],"Ларимэль")) $arr['larimel_text'] = "exist";
                continue;
            }

            if (is_object($value)) continue;
            $arr[$field['name']] = $value;
            $arr['link'] = $element->link;
        }
        return $arr;
    }

    public function listFilterFiles($filter = "", $oper = false){
        $root = CURRENT_WORKING_DIR;
        if (is_dir($root."/images/larimel/")) {
            $files_=scandir($root."/images/larimel/");
            $i = 0;
            foreach ($files_ as $file){
                if (($file==".") or ($file=="..")) continue;
                if (is_dir($root."/images/larimel/".$file."/")) {
                    $files__=scandir($root."/images/larimel/".$file."/");
                    foreach ($files__ as $f){
                        if (($f==".") or ($f=="..")) continue;
                        if ($filter) if (strpos($f,$filter) === false) continue;
                        if ($oper=='del') unlink($root."/images/larimel/".$file."/".$f);
                        echo "/images/larimel/".$file."/".$f.", ";
                    }
                }
            }
        }
        die;
    }

    public function getListLarimelBrend($limit = false){
        $user_id = cmsController::getInstance()->getModule("users")->user_id;
        $hierarchy = umiHierarchy::getInstance();
        $general_page = $hierarchy->getElement(1);
        if ($user_id == 2){
            $pages = new selector('objects');
            $pages -> types('object-type')->id(89);
            $pages -> where('is-active')->equals(true);
            $pages -> order("name");
            $result = $pages->result();
            $arr = array();
            foreach($result as $item){
                //Расчет количества товаров у производителей
                $pages_brend = new selector('pages');
                $pages_brend->types('object-type')->id(88);
                $pages_brend->where('brend_larimel')->equals($item -> id);
                if (!$pages_brend -> length) continue;
                $arr_ = array();
                $arr_['attribute:id'] = $item -> id;
                $arr_['node:name'] = $item -> getName();
                $arr[] = $arr_;
            }
            $general_page = $hierarchy->getElement(1);
            $general_page -> setValue("list_larimel_brend", serialize($arr));
            $general_page -> commit();
        } else $arr = unserialize($general_page -> list_larimel_brend);

        if ($limit) $arr = array_slice($arr,0,$limit);
        return array("nodes:item"=>$arr);
    }

    public function buy($id = false, $set = false, $amount = 1){
        if (isset($_SESSION['basket'])) $basket = unserialize($_SESSION['basket']); else $basket = array();
        $hierarchy = umiHierarchy::getInstance();
        if ($set == "1"){
            if (is_numeric($id)){
                if ($hierarchy->isExists($id)){
                    $element = $hierarchy->getElement($id);
                    if ($element->getObjectTypeId() == 88){
                        $basket[$id] = $amount;
                        $_SESSION['basket'] = serialize($basket);
                        return "1";
                    }
                }
            }
        }

        //Проверка статуса для товара (есть - 1 или нет - 0 в корзине)
        if ($set == "2"){
            if (isset($basket[$id])) return "1";
            return "0";
        }

        //Удалить товар из корзины
        if ($set == "3"){
            if (isset($basket[$id])) {
                unset($basket[$id]);
                $_SESSION['basket'] = serialize($basket);
                return "0";
            }
        }

        //Очистить корзину
        if ($set == "4"){
            unset($_SESSION['basket']);
            return "0";
        }

        if (isset($_SESSION['id_currency'])){
            $listCurrency = $this->listCurrency($_SESSION['id_currency'], true);
            foreach($listCurrency as $currency){
                if (isset($currency['default']))
                    if ($currency['default'] == 1){
                        $koef_price = $currency['exchange'];
                        break;
                    }
            }
        } else{
            $sel_cur = new selector('objects');
            $sel_cur -> types('object-type')->id(79);
            $sel_cur = $sel_cur -> result();
            foreach($sel_cur as $sel_cur_){
                if ($sel_cur_->default == 1){
                    $koef_price = $sel_cur_->exchange;
                    break;
                }
            }
        }

        $result = array();
        foreach($basket as $id=>$wood){
            $getWood = $hierarchy->getElement($id);
            if ($getWood instanceof umihierarchyElement){
                $res = array();
                $res['attribute:id'] = $id;
                $res['attribute:num'] = $wood;
                $res['attribute:price'] = $koef_price * $getWood->price_larimel;
                $res['node:name'] = $getWood -> h1;
                $result[] = $res;
            }
        }
        return array("nodes:item"=>$result);
    }

    public function getPriceCurrentExchange($price = 0){
        if (isset($_SESSION['id_currency'])){
            $listCurrency = $this->listCurrency($_SESSION['id_currency'], true);
            foreach($listCurrency as $currency){
                if (isset($currency['default']))
                    if ($currency['default'] == 1){
                        $koef_price = $currency['exchange'];
                        $shot = $currency['shot'];
                        break;
                    }
            }
        } else{
            $sel_cur = new selector('objects');
            $sel_cur -> types('object-type')->id(79);
            $sel_cur = $sel_cur -> result();
            foreach($sel_cur as $sel_cur_){
                if ($sel_cur_->default == 1){
                    $koef_price = $sel_cur_->exchange;
                    $shot = $sel_cur_ -> shot;
                    break;
                }
            }
        }

        return array("price"=>round($price*$koef_price,2),"currency"=>$shot);
    }

    public function basket(){
        $hierarchy = umiHierarchy::getInstance();
        if (isset($_SESSION['basket'])) $basket = unserialize($_SESSION['basket']); else $basket = array();
        $result = array();
        $total_price = 0;
        $total = 0;
        foreach($basket as $id=>$num){
            $element = $hierarchy->getElement($id);
            if ($element instanceOf umiHierarchyElement){
                $res = array();
                $res['attribute:id'] = $id;
                $res['attribute:name'] = $element -> h1;
                $res['attribute:price'] = $element -> price_larimel;
                $res['attribute:amount'] = $num;
                $total += $num;
                $res['attribute:price_item_total'] = $num * $element -> price_larimel;
                $total_price += $res['attribute:price_item_total'];
                $result[] = $res;
            }
        }
        $total_price = $this->getPriceCurrentExchange($total_price);
        $morphWords = $this -> morphWords($total, "products");

        return array("nodes:item"=>$result, "total"=>$total, "morph"=>$morphWords, "price"=>$total_price['price'], "currency"=>$total_price['currency']);
    }

    public function morphWords($count, $word, $noCount = false) {

        /** Prefix of the current language version */
        $langPrefix = cmsController::getInstance() -> getCurrentLang() -> getPrefix();
        /** Words array */
        $words = array();

        switch ($langPrefix) {
            case 'en':
                $words = array(
                    'products' => array('product', 'products', 'products'),
                    'items'    => array('item', 'items', 'items'),
                    'bonuses'  => array('bonus', 'bonuses', 'bonuses')
                );
                break;
            default:
                $words = array(
                    'products'	=> array('товар', 'товара', 'товаров'),
                    'items'   	=> array('штука', 'штуки', 'штук'),
                    'bonuses' 	=> array('бонус', 'бонуса', 'бонусов'),
                    'old'  	=> array('год', 'года', 'лет'),
                    'day'	=> array('день', 'дня', 'дней')
                );
        }

        if(array_key_exists($word, $words)) {
            list($first, $second, $third) = $words[$word];

            if($count == 1) {

                if (!$noCount) $result = '1 ' . $first;
                else $result = $first;

            } elseif (($count > 20) && (($count % 10) == 1)) {

                if(!$noCount) $result = str_replace('%', $count, '% ' . $first);
                else $result = $first;

            } elseif ((($count >= 2) && ($count <= 4)) || ((($count % 10) >= 2) && (($count % 10) <= 4)) && ($count > 20)) {

                if(!$noCount) $result = str_replace('%', $count, '% ' . $second);
                else $result = $second;

            } else {

                if(!$noCount) $result = str_replace('%', $count, '% ' . $third);
                else $result = $third;
            }

            return $result;
        }
    }

    public function getTypeList($type_id = false){
        if (!is_numeric($type_id) or ($type_id==0) or (!$type_id)) return "";
        $objs = new selector('objects');
        $objs -> types('object-type')->id($type_id);
        $res_objs = $objs->result();
        $result = array();
        foreach($res_objs as $element) $result[] = $element;
        return array("nodes:item"=>$result);
    }

    public function purchase(){
        $basket = $this->basket();
        if ($basket['total'] and ($basket['total'] != 0)) {
            $oC = umiObjectsCollection::getInstance();
            $data = getRequest('data');
            $order_id = $oC -> addObject("order",94);
            $obj = $oC->getObject($order_id);
            $obj->setName("Заказ №".$order_id);
            $obj->setValue("order_n", $order_id);
            $obj->setValue("data", date("d.j.Y G:i",time()));
            foreach($data as $field=>$item){
                $obj->setValue($field, $item);
            }
            $obj->setValue("woods_list", serialize($this->basket()));
            $this->buy(false,4); //Очистить корзину
            $obj->commit();
            return $order_id;
        } else return 0;
    }

    public function setIsActiveLarimel($id=false){
        if ((!$id) or (!is_numeric($id))) return "";
        $hierarchy = umiHierarchy::getInstance();
        $element = $hierarchy->getElement($id);
        if($element instanceOf umiHierarchyElement) {
            $is_active = $element->getIsActive();
            if ($is_active) $element->setIsActive(false); else $element->setIsActive(true);
            $element->commit();
        }
        return "";
    }

    public function redirectIngredients($page_id){
        $hierarchy = umiHierarchy::getInstance();
        $element = $hierarchy->getElement($page_id,true);
        $alt_name = $element->getAltName();

        $pages = new selector('pages');
        $pages -> types('object-type')->id(67);
        $pages -> where('id_old_base')->equals($alt_name);

        if ($pages->length){
            $res = $pages->result();
            $url = $hierarchy -> getPathById($res[0]->id);
            $this->redirect($url);
        }
        return "";
    }

    public function stringLike($str = '', $separator = ",", $need = ''){
        $arr = explode($separator, $str);
        if (in_array($need,$arr)) return "1";
        return "";
    }

    //Парсинг rss гороскопов
    public function getRssHoroscopes(){
        $hierarchy = umiHierarchy::getInstance();
        $get_horo_page = $hierarchy->getElement(32337);
        $list_xml_url = array();
        $list_xml_url['general_horoscope'] = $get_horo_page->general_horoscope_url;
        $list_xml_url['erotic_horoscope'] = $get_horo_page->erotic_horoscope_url;
        $list_xml_url['antigoroskop'] = $get_horo_page->antigoroskop_url;
        $list_xml_url['business_horoscope'] = $get_horo_page->business_horoscope_url;
        $list_xml_url['culinary_horoscope'] = $get_horo_page->culinary_horoscope_url;
        $list_xml_url['love_horoscope'] = $get_horo_page->love_horoscope_url;
        $list_xml_url['mobile_horoscope'] = $get_horo_page->mobile_horoscope_url;

        foreach($list_xml_url as $field=>$xml_url){
            $rss = simplexml_load_file($xml_url);   //Интерпретирует XML-файл в объект
            $sign = new selector('pages');
            $sign -> types('object-type')->id(96);
            $sign -> where('hierarchy')->page(32337)->childs(1);
            $res_sign = $sign->result();
            if ($field == "general_horoscope"){
                $get_horo_page_date = $rss->date->attributes();
                $yesterday = (array) $get_horo_page_date['yesterday'];
                $today = (array) $get_horo_page_date['today'];
                $tomorrow = (array) $get_horo_page_date['tomorrow'];
                $tomorrow02 = (array) $get_horo_page_date['tomorrow02'];
                $get_horo_page -> setValue("data_horoscope", $yesterday[0].",".$today[0].",".$tomorrow[0].",".$tomorrow02[0]);
                $get_horo_page -> commit();
            }
            foreach($res_sign as $page){
                $getPage = $hierarchy->getElement($page->id);
                $getAltName = $getPage -> getAltName();
                foreach($rss->children() as $name=>$child) {
                    if ($getAltName == $name){
                        $str = $child->yesterday.$child->today.$child->tomorrow.$child->tomorrow02;
                        $str = strtr($str, array(PHP_EOL.PHP_EOL => PHP_EOL));
                        $getPage -> setValue($field,$str);
                        $getPage -> commit();
                        break;
                    }
                }
            }
        }
        return true;
    }

	// $sign - знак зодиака
	// $type = com, ero, anti, ... (как в blocks.xsl) - тип гороскопа
	// $day = -1,0,1,2 - вчера, сегодня, завтра, послезавтра
	public function getHoro($sign, $forday='today') {
		$h = umiHierarchy::getInstance();
		$page_id = $h->getIdByPath('/horoscopes/'.$sign);

		if ($page_id === false) return false;
		$page = $h->getElement($page_id);
		$com = $page->getValue('general_horoscope');
		$ero = $page->getValue('erotic_horoscope');
		$anti = $page->getValue('antigoroskop');
		$busi = $page->getValue('business_horoscope');
		$cook = $page->getValue('culinary_horoscope');
		$love = $page->getValue('love_horoscope');
		$mobi = $page->getValue('mobile_horoscope');
		list($foo, $com_yesterday, $com_today, $com_tomorrow, $com_aftertomorrow) = explode(PHP_EOL, $com);
		list($foo, $ero_yesterday, $ero_today, $ero_tomorrow, $ero_aftertomorrow) = explode(PHP_EOL, $ero);
		list($foo, $anti_yesterday, $anti_today, $anti_tomorrow, $anti_aftertomorrow) = explode(PHP_EOL, $anti);
		list($foo, $busi_yesterday, $busi_today, $busi_tomorrow, $busi_aftertomorrow) = explode(PHP_EOL, $busi);
		list($foo, $cook_yesterday, $cook_today, $cook_tomorrow, $cook_aftertomorrow) = explode(PHP_EOL, $cook);
		list($foo, $love_yesterday, $love_today, $love_tomorrow, $love_aftertomorrow) = explode(PHP_EOL, $love);
		list($foo, $mobi_yesterday, $mobi_today, $mobi_tomorrow, $mobi_aftertomorrow) = explode(PHP_EOL, $mobi);
		
		$yesterday_horoes = array();
		$yesterday_horoes[] = array('attribute:type' => 'com', 'text' => $com_yesterday);
		$yesterday_horoes[] = array('attribute:type' => 'ero', 'text' => $ero_yesterday);
		$yesterday_horoes[] = array('attribute:type' => 'anti', 'text' => $anti_yesterday);
		$yesterday_horoes[] = array('attribute:type' => 'busines', 'text' => $busi_yesterday);
		$yesterday_horoes[] = array('attribute:type' => 'cook', 'text' => $cook_yesterday);
		$yesterday_horoes[] = array('attribute:type' => 'love', 'text' => $love_yesterday);
		$yesterday_horoes[] = array('attribute:type' => 'mobi', 'text' => $mobi_yesterday);

		$today_horoes = array();
		$today_horoes[] = array('attribute:type' => 'com', 'text' => $com_today);
		$today_horoes[] = array('attribute:type' => 'ero', 'text' => $ero_today);
		$today_horoes[] = array('attribute:type' => 'anti', 'text' => $anti_today);
		$today_horoes[] = array('attribute:type' => 'busines', 'text' => $busi_today);
		$today_horoes[] = array('attribute:type' => 'cook', 'text' => $cook_today);
		$today_horoes[] = array('attribute:type' => 'love', 'text' => $love_today);
		$today_horoes[] = array('attribute:type' => 'mobi', 'text' => $mobi_today);

		$tomorrow_horoes = array();
		$tomorrow_horoes[] = array('attribute:type' => 'com', 'text' => $com_tomorrow);
		$tomorrow_horoes[] = array('attribute:type' => 'ero', 'text' => $ero_tomorrow);
		$tomorrow_horoes[] = array('attribute:type' => 'anti', 'text' => $anti_tomorrow);
		$tomorrow_horoes[] = array('attribute:type' => 'busines', 'text' => $busi_tomorrow);
		$tomorrow_horoes[] = array('attribute:type' => 'cook', 'text' => $cook_tomorrow);
		$tomorrow_horoes[] = array('attribute:type' => 'love', 'text' => $love_tomorrow);
		$tomorrow_horoes[] = array('attribute:type' => 'mobi', 'text' => $mobi_tomorrow);

		$aftertomorrow_horoes = array();
		$aftertomorrow_horoes[] = array('attribute:type' => 'com', 'text' => $com_aftertomorrow);
		$aftertomorrow_horoes[] = array('attribute:type' => 'ero', 'text' => $ero_aftertomorrow);
		$aftertomorrow_horoes[] = array('attribute:type' => 'anti', 'text' => $anti_aftertomorrow);
		$aftertomorrow_horoes[] = array('attribute:type' => 'busines', 'text' => $busi_aftertomorrow);
		$aftertomorrow_horoes[] = array('attribute:type' => 'cook', 'text' => $cook_aftertomorrow);
		$aftertomorrow_horoes[] = array('attribute:type' => 'love', 'text' => $love_aftertomorrow);
		$aftertomorrow_horoes[] = array('attribute:type' => 'mobi', 'text' => $mobi_aftertomorrow);

		$day["attribute:sign"] = $sign;
		$day["yesterday"] = array("nodes:item"=>$yesterday_horoes);
		$day["today"] = array("nodes:item"=>$today_horoes);
		$day["tomorrow"] = array("nodes:item"=>$tomorrow_horoes);
		$day["aftertomorrow"] = array("nodes:item"=>$aftertomorrow_horoes);
		$days[] = $day;
		// сначала сделал чтобы выводилась вся структура по всем дням
		// потом, надо стала выводить один день
		// сделал так, чтобы не париться и не переделылвать все заново )
		if ($forday == 'yesterday')
			return array("nodes:horoscope"=>$yesterday_horoes);
		else if ($forday == 'today')
			return array("nodes:horoscope"=>$today_horoes);
		else if ($forday == 'tomorrow')
			return array("nodes:horoscope"=>$tomorrow_horoes);
		else if ($forday == 'aftertomorrow')
			return array("nodes:horoscope"=>$aftertomorrow_horoes);
	}
	
	// список ссылок на все гороскопы для главной страницы гороскопа на день $day
	public function allHoroes($day='today') {
		$horo_signes = array(
			array('Овен', 'aries', '21.03 - 20.04'),
			array('Телец', 'taurus', '21.04 - 21.05'),
			array('Близнецы', 'gemini', '22.05 - 21.06'),
			array('Рак', 'cancer', '22.06 - 23.07'),
			array('Лев', 'leo', '24.07 - 23.08'),
			array('Дева', 'virgo', '24.08 - 23.09'),
			array('Весы', 'libra', '24.09 - 23.10'),
			array('Скорпион', 'scorpio', '24.10 - 22.11'),
			array('Стрелец', 'sagittarius', '23.11 - 21.12'),
			array('Козерог', 'capricorn', '22.12 - 20.01'),
			array('Водолей', 'aquarius', '21.01 - 19.02'),
			array('Рыбы', 'pisces', '20.02 - 20.03')
		);
		$horo_types = array(
			array('Общий гороскоп', 'com'),
			array('Эротический гороскоп', 'ero'),
			array('Антигороскоп', 'anti'),
			array('Бизнес-гороскоп', 'busines'),
			array('Кулинарный гороскоп', 'cook'),
			array('Любовный гороскоп', 'love'),
			array('Мобильный гороскоп', 'mobi')
		);

		$types = array();
		foreach ($horo_types as $ht) {
			$type = array();
			$type['attribute:name'] = $ht[1];
			$type['attribute:title'] = $ht[0];
			$signes = array();
			foreach ($horo_signes as $hs) {
				$sign = array();
				$sign['attribute:name'] = $hs[1];
				$sign['attribute:title'] = $hs[0];
				$sign['attribute:dates'] = $hs[2];
				$sign['attribute:link'] = '/horoscope/'.$hs[1].'/'.$day.'#'.$ht[1];
				$signes[] = $sign;
			}
			$type['nodes:sign'] = $signes;
			$types[] = $type;
		}
		
		return array("nodes:type"=>$types);
	}

    //Автосохранение текста в админке при редактировании
    public function autoSaveContentArtice($page_id = false){
        $page_id = is_numeric($page_id) ? $page_id : false;
        if (!$page_id) return "";
        $content = getRequest("content");
        $hierarchy = umiHierarchy::getInstance();
        $getPage = $hierarchy->getElement($page_id);
        if($getPage instanceOf umiHierarchyElement) {
            $old_mode = umiObjectProperty::$IGNORE_FILTER_INPUT_STRING;		//Откючение html сущн.
            umiObjectProperty::$IGNORE_FILTER_INPUT_STRING = true;
            $getPage->setValue("content",$content);
            $getPage->commit();
            umiObjectProperty::$IGNORE_FILTER_INPUT_STRING = $old_mode;		//Вкл. html сущн.
        }
        return "";
    }



    //Обработчик события - изменение страницы в админке
    public function change_page(iUmiEventPoint $oEventPoint){
        if ($oEventPoint->getMode() === "before") return true;

        if ($oEventPoint->getMode() === "after") {
            // берем необходимые параметры
            $ElementId = $oEventPoint->getRef('element')->getId();

            //Если дата не установлена, сохраняется текущая
            $hierarchy = umiHierarchy::getInstance();
            $getPage = $hierarchy->getElement($ElementId);
            if ($getPage instanceof umiHierarchyElement){
                if($getPage->getObjectTypeId()==66){
                    $getDate= $getPage -> time;
                    if (!$getDate) {
                        $getPage->setValue('time',time());
                        $getPage->commit();
                    }
                    //Пересчет рецептов и подбор релевантных
                    $this->recipes_recount($ElementId);
                    $this->form_recipes_relevant($ElementId);
                }
            }
        }
        return;
    }



    //Обновление списка типов данных в справочнике "Типы данных - страниц"
    public function updateTypesList(){
        $oC = umiObjectsCollection::getInstance();
        $typesCollection = umiObjectTypesCollection::getInstance();
        $listTypes = $typesCollection->getTypesByHierarchyTypeId(27);

        $typesExist = array();
        $types = new selector('objects');
        $types -> types('object-type')->id(101);
        $types_exist = $types->result();
        foreach($types_exist as $type){
            $typesExist[$type->type_id] = $type->getName();
        }

        foreach($listTypes as $id=>$typeName){
            if (!isset($typesExist[$id])) {
                $object_id = $oC -> addObject($typeName,101);
                $getObject = $oC->getObject($object_id);
                $getObject->setValue("type_id", $id);
                $getObject->commit();
            }
        }

        return "";
    }

    public function generate(){
        $len = 100;         //Длина предложения
        $letters = array(
            'а' => 9,   'б' => 7,   'в' => 4,   'г' => 2,   'д' => 1.5,   'е' => 8.1,   'ж' => 3,   'з' => 3,
            'и' => 9,   'й' => 7,   'к' => 4,   'л' => 2,   'м' => 1.5,   'н' => 2.5,   'о' => 10,   'п' => 3,
            'р' => 9,   'с' => 7,   'т' => 4,   'у' => 2,   'ф' => 1.5,   'х' => 2.5,   'ц' => 3,   'ч' => 3,
            'э' => 1,   'ю' => 2,   'я' => 1
        );
        $glas = array(
            'а', 'о', 'э', 'и', 'у', 'е', 'ю', 'я'
        );

        $amount_letter = array();
        foreach($letters as $letter=>$prob) {
            $amount_letter[$letter] = $prob * $len / 100;
        }

        $letters = array();
        foreach($amount_letter as $letter=>$amount) {
            for($i = 0; $i<$amount; $i++) $letters[] = $letter;
        }

        //Перемешиваем буквы в полученном массиве
        shuffle($letters);
        $str = '';

        $first_letter = true;
        $current_letter = '';
        $total_letters = count($letters);
        for($index = 0; $index<$total_letters; $index++){
            if ($first_letter){
                $first_letter = false;
                $probability = array();
                for($i = 0; $i < count($letters); $i++){
                    $prob = 100;
                    $prob = (in_array($letters[$i], $glas)) ? $prob * 0.1 : $prob * 0.9;
                    $probability[$i] = $prob;
                }
                arsort($probability);
                $getIdLetter = key($probability);
                $str .= $letters[$getIdLetter];
                $current_letter = $letters[$getIdLetter];
                unset($letters[$getIdLetter]);
            } else {
                $probability = array();
                foreach($letters as $i=>$letter){
                    $prob = 100;
                    //Если гласная после гласной
                    if (in_array($current_letter, $glas) && in_array($letters[$i], $glas)) $prob = $prob * 0.1;
                    if (!in_array($current_letter, $glas) && in_array($letters[$i], $glas)) $prob = $prob * 0.75;
                    if (in_array($current_letter, $glas) && !in_array($letters[$i], $glas)) $prob = $prob * 0.90;
                    if (!in_array($current_letter, $glas) && !in_array($letters[$i], $glas)) $prob = $prob * 0.25;
                    $probability[$i] = $prob;
                }
                arsort($probability);
                $getIdLetter = key($probability);
                $str .= $letters[$getIdLetter];
                $current_letter = $letters[$getIdLetter];
                unset($letters[$getIdLetter]);
            }
        }
        echo $str. "   ";
        die;



    }

    //Получить содержимое рецепта
    public function getRecipeContent($recipeId){
        $hierarchy = umiHierarchy::getInstance();
        $element = $hierarchy -> getElement($recipeId);
        if ($element instanceof umiHierarchyElement) {
            $content = $element -> getValue('content');
            $content = strtr($content, array("http://gotovimvse.com/"=>"/"));
            return $content;
		}
		return "";
	}

}

?>
