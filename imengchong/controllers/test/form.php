<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 imengchong.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 

class form extends MC_Controller {

    public function photo_upload() {
        echo '<html>';
        echo '<head>';
        echo '<title>Upload Form</title>';
        echo '</head>';
        echo '<body>';
        echo '<form method="post" action="/cm/photo_upload" enctype="multipart/form-data" />';
        echo '<input type="file" name="photo" size="20" />';
        echo '<br /><br />';
        echo '<input type="submit" value="upload" />';
        echo '</form>';
        echo '</body>';
        echo '</html>';
    }

    public function animal_avatar_upload() {
        echo '<html>';
        echo '<head>';
        echo '<title>Animal Avatar Upload</title>';
        echo '</head>';
        echo '<body>';
        echo '<form method="post" action="/cm/animal_avatar_upload" enctype="multipart/form-data" />';
        echo '<input type="file" name="animal_avatar" size="20" />';
        echo '<br /><br />';
        echo '<input type="submit" value="upload" />';
        echo '</form>';
        echo '</body>';
        echo '</html>';
    }

    public function create_photo() {
        $this->load->model('Animal_Model');
        $user_animal = $this->Animal_Model->get_animal_id_by_user_id(array('user_id'=>$this->userinfo['user_id']));
        $animal_ids =  array_col_val($user_animal, 'animal_id');
        $animal_detail = $this->Animal_Model->get_animal_info_batch(array('animal_ids'=>$animal_ids));
        echo '<html>';
        echo '<head>';
        echo '<title>Create Photo</title>';
        echo '</head>';
        echo '<body>';
        echo '<form method="post" action="/cm/create_photo" enctype="multipart/form-data" />';
        echo '<p>图片数: <input type="text" name="photo_num" /></p>';
        echo '<p>图片id,输入格式字符串数字+逗号,如:8,9,10,11: <input type="text" name="store_ids" /></p>';
        echo '<p>图片描述: <input type="text" name="description" /></p>';
        echo '<p>图片标签,输入格式字符串数字+逗号,如:1,2,3,4 : <input type="text" name="tag_ids" /></p>';
        echo '<p>图片来源id(2,3,4,5,1=>我家,朋友,互联网收集,街拍,其他) : <input type="text" name="source" /></p>';
        echo '<p>这是我家的宠物id: <input type="text" name="animal_id" /></p>';
        echo '<p>目前我家的宠物:</p>';
        echo '<p>';
        var_dump($animal_detail);
        echo '</p>';
        echo '<input type="submit" value="create_photo" />';
        echo '</form>';
        echo '</body>';
        echo '</html>';
    }
    
    public function quick_create_animal() {
        echo '<html>';
        echo '<head>';
        echo '<title>Create Photo</title>';
        echo '</head>';
        echo '<body>';
        echo '<form method="post" action="/cm/create_animal_quick" enctype="multipart/form-data" />';
        echo '<p>Ta是(1,2,3=>火星人,喵星人,汪星人): <input type="text" name="type" /></p>';
        echo '<p>Ta叫: <input type="text" name="animal_name" /></p>';
        echo '<p>性别(1,2=>公主,王子): <input type="text" name="sex" /></p>';
        echo '<input type="submit" value="quick_create_animal" />';
        echo '</form>';
        echo '</body>';
        echo '</html>';
    }

}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
