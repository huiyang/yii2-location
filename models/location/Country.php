<?php

namespace humanized\location\models\location;

use humanized\translation\models\Translation;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "country".
 *
 * @property string $iso_2
 * @property string $iso_3
 * @property integer $iso_numerical
 * @property Location[] $locations
 */
class Country extends \yii\db\ActiveRecord
{

    public $label;
    public $code;
    public $common_name;
    public $official_name;
	
	protected static $_countries;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'country';
    }

    public function fields()
    {
        return [
            // field name is the same as the attribute name
            'code', 'common_name', 'official_name', 'has_postcodes',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['iso_2', 'iso_3', 'iso_numerical', 'has_postcodes'], 'required'],
            [['iso_numerical'], 'integer'],
            [['has_postcodes'], 'integer', 'max' => 1],
            [['postcode_mask'], 'string', 'max' => 100],
            [['iso_2'], 'string', 'max' => 2],
            [['iso_3'], 'string', 'max' => 3],
                //[['name', 'adjectival', 'demonym'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'iso_2' => 'Iso 2',
            'iso_3' => 'Iso 3',
            'iso_numerical' => 'Iso Numerical',
            'name' => 'Country',
            'adjectival' => 'Adjectival',
            'demonym' => 'Demonym',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocations()
    {
        return $this->hasMany(Location::className(), ['country_id' => 'iso_2']);
    }

    public static function available()
    {
		if (!isset(self::$_countries)) {
			$searchModel = (new Country())->_query();
			self::$_countries = $searchModel->asArray()->all();
		}

        return self::$_countries;
    }

    public static function dropdown()
    {
        $data = self::available();
        return ArrayHelper::map($data, 'code', 'label');
    }

    public static function enabled()
    {
        
    }
	
	protected function getModuleByClassName($class) {
		$modules = \Yii::$app->getModules();
		foreach ($modules as $id => $module) {
			if ($module['class'] == $class) {
				return $id;
			}
		}
	}

    protected function _query()
    {
        $query = Country::find();
        $currentLanguage = substr(\Yii::$app->language, 0, 2);
		
		$module = \Yii::$app->getModule($this->getModuleByClassName(\humanized\location\Module::className()));
		
        $local = new Expression("'$currentLanguage'");
        $fallbackLanguage = substr($module->defaultLanguage, 0, 2);
        $fallback = new Expression("'$fallbackLanguage'");

        $query->leftJoin('country_translation default_label', "(`country`.`iso_2`=`default_label`.`country_id` AND `default_label`.`language_id` = $fallback)");
        $query->leftJoin('country_translation localised_label', "(`country`.`iso_2`=`localised_label`.`country_id` AND `localised_label`.`language_id` = $local)");
        $query->select = [
            'code' => 'iso_2',
            'label' => 'CONCAT(IF(localised_label.common_name IS NULL, default_label.common_name,localised_label.common_name),\' (\',iso_2,\')\')',
            'has_postcodes' => 'has_postcodes',
            'common_name' => 'IF(localised_label.common_name IS NULL, default_label.common_name,localised_label.common_name)',
            'official_name' => 'IF(localised_label.official_name IS NULL, default_label.official_name,localised_label.official_name)',
            'common' => 'IF(localised_label.common_name IS NULL, default_label.common_name,localised_label.common_name)',
            'official' => 'IF(localised_label.official_name IS NULL, default_label.official_name,localised_label.official_name)',
        ];
        $query->groupBy('code');
        return $query;
    }

}
