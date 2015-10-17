<div id="frontpage-content">

    <div class="modal">
        <div class="header">
                <?php echo $theme->Html->image('welcome.png', array('parent' => true)); ?>
                <p>
                    Please select one of our campuses below.
                </p>
        </div>
        <ul class="campus-list campus-chooser">
            <li style="background-image: url('/wp-content/themes/costamesa/img/map-sm.jpg');">
                <div class="campus-info">
                    <div class="title">
                        <a href="http://costamesa.rockharbor.org" class="campus-link">Costa Mesa</a>
                    </div>
                    <div class="service-times">
                        <?php echo do_shortcode('[service-times campus="9"]'); ?>
                    </div>
                </div>
            </li>
            <li style="background-image: url('/wp-content/themes/missionviejo/img/map-sm.jpg');">
                <div class="campus-info">
                    <div class="title">
                        <a href="http://missionviejo.rockharbor.org" class="campus-link">Mission Viejo</a>
                    </div>
                    <div class="service-times">
                        <?php echo do_shortcode('[service-times campus="6"]'); ?>
                    </div>
                </div>
            </li>
            <li style="background-image: url('/wp-content/themes/fullerton/img/map-sm.jpg');">
                <div class="campus-info">
                    <div class="title">
                        <a href="http://fullerton.rockharbor.org" class="campus-link">Fullerton</a>
                    </div>
                    <div class="service-times">
                        <?php echo do_shortcode('[service-times campus="5"]'); ?>
                    </div>
                </div>
            </li>
            <li style="background-image: url('/wp-content/themes/huntingtonbeach/img/map-sm.jpg');">
                <div class="campus-info">
                    <div class="title">
                        <a href="http://huntingtonbeach.rockharbor.org" class="campus-link">Huntington Beach</a>
                    </div>
                    <div class="service-times">
                        <?php echo do_shortcode('[service-times campus="7"]'); ?>
                    </div>
                </div>
            </li>
            <li style="background-image: url('/wp-content/themes/orange/img/map-sm.jpg');">
                <div class="campus-info">
                    <div class="title">
                        <a href="http://orange.rockharbor.org" class="campus-link">Orange</a>
                    </div>
                    <div class="service-times">
                        <?php echo do_shortcode('[service-times campus="8"]'); ?>
                    </div>
                </div>
            </li>
            <li style="background-image: url('/wp-content/themes/charlotte/img/map-sm.jpg');">
                <div class="campus-info">
                    <div class="title">
                        <a href="http://charlotte.rockharbor.org" class="campus-link">Charlotte, NC</a>
                    </div>
                    <div class="service-times">
                        <?php echo do_shortcode('[service-times campus="19"]'); ?>
                    </div>
                </div>
            </li>
        </ul>
        <a href="javascript:RH.hideSplash('www');" class="continue">
            OR CONTINUE TO <span>ROCK</span>HARBOR.ORG
        </a>
    </div>
</div>
