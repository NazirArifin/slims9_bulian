<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-23T11:27:04+07:00
# @Email:  ido.alit@gmail.com
# @Filename: _home.php
# @Last modified by:   user
# @Last modified time: 2018-01-26T18:43:45+07:00
require_once SB.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/news_template.php';
require_once LIB.'content.inc.php';

$content = new content();
$content_list = $content->getContents($dbs, 3);
?>

<section id="section1 container-fluid">
    <header class="c-header">
        <div class="mask"></div>
      <?php
      // ------------------------------------------------------------------------
      // include navbar
      // ------------------------------------------------------------------------
      include '_navbar.php'; ?>
    </header>
  <?php
  // --------------------------------------------------------------------------
  // include search form part
  // --------------------------------------------------------------------------
  include '_search-form.php'; ?>
</section>

<div id="slims-home">
<!-- <section class="mt-5 container">
    <h4 class="text-secondary text-center text-thin mt-5 mb-4"><?php echo __('Select the topic you are interested in'); ?></h4>
    <ul class="topic d-flex flex-wrap justify-content-center px-0">
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=8&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/8-books.png'); ?>" width="80" class="mb-3 mx-auto"/>
                <?php echo __('Literature'); ?>
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=3&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/3-diploma.png'); ?>" width="80" class="mb-3 mx-auto"/>
                <?php echo __('Social Sciences'); ?>
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=6&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/6-blackboard.png'); ?>" width="80" class="mb-3 mx-auto"/>
                <?php echo __('Applied Sciences'); ?>
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=7&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/7-quill.png'); ?>" width="80" class="mb-3 mx-auto"/>
                <?php echo __('Art & Recreation'); ?>
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="javascript:void(0)" class="d-flex flex-column" data-toggle="modal" data-target="#exampleModal">
                <img src="<?php echo assets('images/icon/grid_icon.png'); ?>" width="80"
                     class="mb-3 mx-auto"/>
                <?php echo __('see more..'); ?>
            </a>
        </li>
    </ul>
</section> -->

<?php if ($sysconf['template']['classic_popular_collection']) : ?>
<section class="mt-5 container">
    <h4 class=" mb-4">
        <?php echo __('Popular among our collections'); ?>
        <br>
        <small class="subtitle-section"><?php echo __('Our library\'s line of collection that have been favoured by our users were shown here. Look for them. Borrow them. Hope you also like them');?></small>
    </h4>

    <slims-group-subject url="index.php?p=api/subject/popular"></slims-group-subject>
    <slims-collection url="index.php?p=api/biblio/popular"></slims-collection>

</section>
<?php endif; ?>

<?php if ($sysconf['template']['classic_new_collection']) : ?>
<section class="mt-5 container">
    <h4 class=" mb-4">
        <?php echo __('New collections + updated');?>
        <br>
        <small class="subtitle-section"><?php echo __('These are new collections list. Hope you like them. Maybe not all of them are new. But in term of time, we make sure that these are fresh from our processing oven');?></small>
    </h4>

    <slims-group-subject url="index.php?p=api/subject/latest"></slims-group-subject>
    <slims-collection url="index.php?p=api/biblio/latest"></slims-collection>

</section>
<?php endif; ?>

<section class="mt-5 container">
  <h4 class="mb-4">
    <?php echo __('Library News') ?>
  </h4>

  <?php
  foreach ($content_list as $c) {
    echo news_list_tpl($c['content_title'], $c['content_path'], $c['publish_date'] ?? $c['last_update'], Content::createSummary($c['content_desc'], 300));
  }
  ?>

  <div class="text-center">
    <a href="index.php?p=news" class="btn btn-outline-info"><?php echo __('Show'), ' ', __('News'); ?></a>
  </div>
</section>

<?php if ($sysconf['template']['classic_top_reader']) : ?>
<section class="mt-5 bg-white">
    <div class="container pt-5 pb-3">
        <h4 class="mb-4">
            <?php echo __('Top reader of the year');?>
            <br>
            <small class="subtitle-section"><?php echo __('Our best users, readers, so far. Continue to read if you want your name being mentioned here');?></small>
        </h4>
        <slims-group-member url="index.php?p=api/member/top"></slims-group-member>
    </div>
</section>
<?php endif; ?>

<section class="mt-0 pb-5 bg-white">
    <div class="container pb-5 pt-3">
        <h4 class="mb-5">
            <?php echo __('Online Journal');?>
            <!-- <br>
            <small class="subtitle-section"><?php echo __('Our online journal collection. Hope you enjoy it.');?></small> -->
        </h4>
        <div class="flex" style="justify-content: center; align-items: center;">
            <a href="//ejournal.unira.ac.id" target="_blank"><img style="height: 90px;" class="mx-2" src="/images/default/logo.png" alt="ejournal unira"></a>
            <a href="//search.epnet.com" target="_blank"><img style="height: 80px;" class="mx-2" src="/images/default/logo_ehost.png" alt="ehost"></a>
            <a href="//www.emeraldinsight.com/page/openaccess" target="_blank"><img style="height: 80px;" class="mx-2" src="/images/default/emerald-logo.png" alt="emerald"></a>
            <a href="//http://galeauth.galegroup.com/auth/capmAuthentication.do?userGroupName=kpt07046&origURL=http%3A%2F%2Fgaleapps.galegroup.com%2Fapps%2Fauth%2Fkpt07046%3Fcause%3Dhttp%253A%252F%252Ffind.galegroup.com%252Fmenu%252Fcommonmenu.do%253FuserGroupName%253Dkpt07046%2526finalAuth%253Dtrue%26authCount%3D1&isCookieCheckDone=false" target="_blank"><img style="height: 80px;" class="mx-2" src="/images/default/gale.png" alt="gale"></a>
            <a href="//scholar.google.co.id" target="_blank"><img style="height: 80px;" class="mx-2" src="/images/default/Google_Scholar.png" alt="Google Scholar"></a>
            <a href="//https://www.sciencedirect.com/search?qs=management&show=25&sortBy=relevance&accessTypes=openaccess&lastSelectedFacet=accessTypes" target="_blank"><img style="height: 80px;" class="mx-2" src="/images/default/Science-Directs.png" alt="Science Direct"></a>
        </div>
    </div>
</section>

<?php if ($sysconf['template']['classic_map']) : ?>
<section class="my-5 container">
    <div class="row align-items-center">
        <div class="col-md-6">
            <iframe class="embed-responsive"
                    src="<?= $sysconf['template']['classic_map_link']; ?>"
                    height="420" frameborder="0" style="border:0" allowfullscreen></iframe>
        </div>
        <div class="col-md-6 pt-8 md:pt-0">
            <h4><?= $sysconf['library_name']; ?></h4>
            <p><?= $sysconf['template']['classic_map_desc']; ?></p>
            <p class="d-flex flex-row pt-2">
                <a target="_blank" href="<?= $sysconf['template']['classic_fb_link'] ?>" class="btn btn-primary mr-2" name="button"><i class="fab fa-facebook-square text-white"></i></a>
                <a target="_blank" href="<?= $sysconf['template']['classic_twitter_link'] ?>" class="btn btn-info mr-2" name="button"><i class="fab fa-twitter-square text-white"></i></a>
                <a target="_blank" href="<?= $sysconf['template']['classic_youtube_link'] ?>" class="btn btn-danger mr-2" name="button"><i class="fab fa-youtube text-white"></i></a>
                <a target="_blank" href="<?= $sysconf['template']['classic_instagram_link'] ?>" class="btn btn-dark mr-2" name="button"><i class="fab fa-instagram text-white"></i></a>
            </p>
        </div>
    </div>
</section>
<?php endif; ?>
</div>