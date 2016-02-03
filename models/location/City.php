<?php

namespace humanized\location\models\location;

use humanized\location\models\translation\CityTranslation;
use humanized\translation\models\Language;
use Yii;

/**
 * This is the model class for table "city".
 *
 * @property integer $id
 * @property string $language_id
 *
 * @property Language $language
 * @property CityTranslation[] $cityTranslations
 * @property Language[] $languages
 * @property Location[] $locations
 */
class City extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language_id'], 'required'],
            [['language_id'], 'string', 'max' => 5],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['language_id' => 'code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'language_id' => Yii::t('app', 'Language ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['code' => 'language_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCityTranslations()
    {
        return $this->hasMany(CityTranslation::className(), ['city_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultIdentification()
    {
        return $this->hasOne(CityTranslation::className(), ['city_id' => 'id'])->onCondition(['`city_translation`.`language_id`' => '`city`.`language_id`']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocalisedIdentification()
    {
        return $this->hasOne(CityTranslation::className(), ['city_id' => 'id'])->onCondition(['`city_translation`.`language_id`' => '`city`.`language_id`']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages()
    {
        return $this->hasMany(Language::className(), ['code' => 'language_id'])->viaTable('city_translation', ['city_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocations()
    {
        return $this->hasMany(Location::className(), ['city_id' => 'id']);
    }

}