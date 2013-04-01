<div class="container_12 masonry_container" id="main">
  
  <div class="grid_3 mc_grid">
    <div class="sheet content">
      <ul class="filter" id="category_filter">
        <li><a href="#">全部</a></li>
        <li><a href="#">喵星人</a></li>
        <li><a href="#">汪星人</a></li>
      </ul>
    </div>
    <div class="content">
      &nbsp;
    </div>
  </div>
  <!--
  'photo_id' => string '30' (length=2)
  'animal_id' => string '8' (length=1)
  'user_id' => string '1' (length=1)
  'store_ids' => string '{"store_ids":[1,2,3,4,5]}' (length=25)
  'tag_ids' => string '{"tag_ids":[1,2]}' (length=17)
  'create_time' => string '1332863732' (length=10)
  'description' => string 'description_498662261' (length=21)
  'photo_num' => string '5' (length=1)
  'source' => string '1' (length=1)
  'type' => string '1' (length=1)
  'meng_count' => string '0' (length=1)
  'share_record' => string '{}' (length=2)
  'animal_name' => string '濡欏_create_animal1674708370' (length=30)
  'user_name' => string 'aaa' (length=3)
  'comment_count' => int 0
  'comment' => 
    array (size=0)
      empty
  -->
  <?php foreach ($photo_detail as $item) { ?>
  <div class="grid_3 mc_grid">
    <div class="mc_grid_content">
      <!-- <img src="/image/photo?p=<?php printf("%s",$item['photo_id']) ?>&s=m"> -->
      <img src="/image/photo?p=8&s=m">
      <p class="intro"><?php echo $item['description'] ?></p>
    </div>
    <div class="mc_grid_footer">
      <?php foreach ($item['comment'] as $comment) { ?>
      <p><a href="#">这是apple</a>：它又在偷吃我的哈密瓜了</p>
      <p><a href="#">小公主</a>：长得好像miumiu啊</p>
      <?php } ?>
    </div>
  </div>
  <?php } ?>
</div>
