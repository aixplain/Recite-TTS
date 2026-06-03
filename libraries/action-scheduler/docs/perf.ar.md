---
title: المعالجة في الخلفية على نطاق واسع في WordPress - صف مهام Action Scheduler
description: تعلّم كيفية إجراء المعالجة في الخلفية على نطاق واسع في WordPress من خلال ضبط مُشغِّل WP Cron الافتراضي لصف مهام Action Scheduler.
---
# المعالجة في الخلفية على نطاق واسع

صُمِّمت المعالجة الافتراضية في Action Scheduler للعمل بموثوقية عبر جميع بيئات الاستضافة المختلفة. ولتحقيق ذلك، فإن عتبات المعالجة الافتراضية أكثر تحفظًا مما تستطيع العديد من الخوادم دعمه.

تحديدًا، لن يعالج Action Scheduler الإجراءات في طلب واحد إلا حتى:

* استخدام 90% من الذاكرة المتاحة
* تجاوز معالجة 3 إجراءات إضافية لمدة 30 ثانية من إجمالي وقت الطلب، بناءً على متوسط وقت المعالجة للدُفعة الحالية
* في صف متزامن واحد

بينما يبدأ Action Scheduler طلب استرجاع غير متزامن لمعالجة إجراءات إضافية، إلا أنه في المواقع ذات الصفوف الكبيرة جدًا، قد يؤدي ذلك إلى بطء أوقات المعالجة.

على الرغم من أن استخدام [WP CLI لمعالجة الصفوف](/wp-cli/) هو أفضل نهج لزيادة سرعة المعالجة، إلا أنه في بعض الأحيان لا يكون خيارًا متاحًا. في هذه الحالات، يمكن أيضًا زيادة عتبات المعالجة في Action Scheduler لرفع معدل معالجة الإجراءات بواسطة مُشغِّل الصف الافتراضي.

## زيادة الحد الزمني

افتراضيًا، لن يعالج Action Scheduler الإجراءات لأكثر من 30 ثانية كحد أقصى في كل طلب. يقلل هذا الحد الزمني من خطر انتهاء مهلة البرنامج النصي في بيئات الاستضافة غير المعروفة، والتي يفرض بعضها مهلة 30 ثانية.

إذا كنت تعلم أن مضيفك يدعم أكثر من هذا الحد الزمني لطلبات الويب، يمكنك زيادته. يتيح ذلك معالجة المزيد من الإجراءات في كل طلب ويقلل التأخير بين معالجة كل صف، مما يسرّع بشكل كبير معدل معالجة الإجراءات المجدولة.

على سبيل المثال، سيزيد المقتطف التالي الحد الزمني إلى دقيقتين (120 ثانية):

```php
function eg_increase_time_limit( $time_limit ) {
	return 120;
}
add_filter( 'action_scheduler_queue_runner_time_limit', 'eg_increase_time_limit' );
```

بعض حدود وقت الاستضافة المعروفة:

* 60 ثانية على WP Engine
* 120 ثانية على Pantheon
* 120 ثانية على SiteGround

## زيادة حجم الدُفعة

افتراضيًا، سيطالب Action Scheduler بدُفعة من 25 إجراءً. هذا الحجم الصغير للدُفعة يعود إلى أن الحد الزمني الافتراضي هو 30 ثانية فقط؛ ومع ذلك، إذا كنت تعلم أن إجراءاتك تُعالج بسرعة كبيرة، مثلاً تستغرق ميكروثوانٍ وليس ثوانٍ، أو أن لديك أكثر من 30 ثانية متاحة لمعالجة كل دُفعة، فإن زيادة حجم الدُفعة يمكن أن تحسّن الأداء بشكل طفيف.

يعود ذلك إلى أن المطالبة بدُفعة لها تكلفة إضافية، لذا كلما قلّت الحاجة إلى المطالبة بدُفعة، زادت سرعة معالجة الإجراءات.

على سبيل المثال، لزيادة حجم الدُفعة إلى 100، يمكننا استخدام الدالة التالية:

```php
function eg_increase_action_scheduler_batch_size( $batch_size ) {
	return 100;
}
add_filter( 'action_scheduler_queue_runner_batch_size', 'eg_increase_action_scheduler_batch_size' );
```

## زيادة الدُفعات المتزامنة

افتراضيًا، سيُشغِّل Action Scheduler دُفعة متزامنة واحدة فقط من الإجراءات. وذلك لمنع استهلاك عدد كبير من الاتصالات أو العمليات المتاحة على خادم الويب الخاص بك.

ومع ذلك، قد يسمح خادمك بعدد كبير من الاتصالات، على سبيل المثال، لأنه يحتوي على قيمة عالية لإعداد `MaxClients` في Apache أو إعداد `pm.max_children` في PHP-FPM.

إذا كان الأمر كذلك، يمكنك استخدام مرشح `'action_scheduler_queue_runner_concurrent_batches'` لزيادة عدد الدُفعات المتزامنة المسموح بها، وبالتالي تسريع معالجة أعداد كبيرة من الإجراءات المجدولة للمعالجة في وقت واحد.

على سبيل المثال، لزيادة العدد المسموح به من الصفوف المتزامنة إلى 10، يمكننا استخدام الكود التالي:

```php
function eg_increase_action_scheduler_concurrent_batches( $concurrent_batches ) {
	return 10;
}
add_filter( 'action_scheduler_queue_runner_concurrent_batches', 'eg_increase_action_scheduler_concurrent_batches' );
```

> تحذير: نظرًا لأن مُشغِّل الصف غير المتزامن المُقدَّم في Action Scheduler 3.0 سيستمر في إرسال طلبات استرجاع غير متزامنة لمعالجة الإجراءات، فإن زيادة عدد الدُفعات المتزامنة يمكن أن تزيد حمل الخادم بشكل كبير وتُسقط الموقع. يُعد [WP CLI](/wp-cli/) طريقة أفضل لتحقيق إنتاجية أعلى.

## زيادة معدل تهيئة المُشغِّلات

افتراضيًا، يبدأ Action Scheduler مُشغِّل صف واحدًا على الأكثر في كل مرة يُطلق فيها الإجراء `'action_scheduler_run_queue'` بواسطة WP Cron.

نظرًا لأن هذا الإجراء لا يُطلق إلا مرة واحدة على الأكثر كل دقيقة، فنادرًا ما سيكون هناك أكثر من صف واحد يعالج الإجراءات حتى لو تم زيادة عدد المُشغِّلات المتزامنة.

للتعامل مع الصفوف الأكبر على الخوادم الأقوى، يمكن بدء مُشغِّلات صف إضافية كلما تم تشغيل الإجراء `'action_scheduler_run_queue'`.

يمكن تحقيق ذلك عن طريق بدء طلبات استرجاع آمنة إضافية إلى خادمنا.

يوضح الكود أدناه كيفية إنشاء 5 طلبات استرجاع في كل مرة يبدأ فيها صف:

```php
/**
 * Trigger 5 additional loopback requests with unique URL params.
 */
function eg_request_additional_runners() {

	// allow self-signed SSL certificates
	add_filter( 'https_local_ssl_verify', '__return_false', 100 );

	for ( $i = 0; $i < 5; $i++ ) {
		$response = wp_remote_post( admin_url( 'admin-ajax.php' ), array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => false,
			'headers'     => array(),
			'body'        => array(
				'action'     => 'eg_create_additional_runners',
				'instance'   => $i,
				'eg_nonce' => wp_create_nonce( 'eg_additional_runner_' . $i ),
			),
			'cookies'     => array(),
		) );
	}
}
add_action( 'action_scheduler_run_queue', 'eg_request_additional_runners', 0 );

/**
 * Handle requests initiated by eg_request_additional_runners() and start a queue runner if the request is valid.
 */
function eg_create_additional_runners() {

	if ( isset( $_POST['eg_nonce'] ) && isset( $_POST['instance'] ) && wp_verify_nonce( $_POST['eg_nonce'], 'eg_additional_runner_' . $_POST['instance'] ) ) {
		ActionScheduler_QueueRunner::instance()->run();
	}

	wp_die();
}
add_action( 'wp_ajax_nopriv_eg_create_additional_runners', 'eg_create_additional_runners', 0 );
```

> تحذير: بسبب معدل معالجة الإجراءات المجدولة، يمكن لهذا النوع من الزيادة أن يُسقط الموقع بسهولة كبيرة. استخدمه فقط على الخوادم عالية القدرة وتأكد من الاختبار قبل محاولة استخدامه في بيئة الإنتاج.

## إضافة الحجم العالي

ليس من الضروري إضافة كل هذا الكود بنفسك، فهناك إضافة مفيدة للوصول إلى كل هذه التحسينات - إضافة [Action Scheduler - High Volume](https://github.com/woocommerce/action-scheduler-high-volume).

<!-- ملاحظات الترجمة: [batch→دُفعة حسب المسرد] | [runner→مُشغِّل حسب المسرد] | [queue→صف بالمعنى التقني لصف المهام] | [loopback request→طلب استرجاع — مصطلح شبكات] | [kept EN: Action Scheduler — اسم مشروع/علامة تجارية] | [kept EN: WP Cron, WP CLI, WP Engine, Pantheon, SiteGround, Apache, PHP-FPM — أسماء تقنيات وعلامات تجارية] | [kept EN: filter hooks and function names — أسماء دوال لا تُترجم] -->