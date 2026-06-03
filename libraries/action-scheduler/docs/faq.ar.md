## الأسئلة الشائعة

### هل من الآمن إصدار Action Scheduler في إضافتي؟ ألن تتعارض دواله مع نسخة أخرى من المكتبة؟

صُمِّم Action Scheduler ليُستخدم ويُصدر ضمن الإضافات. فهو يتجنب إعادة الإعلان عن دوال API العامة عندما يتم تحميل أكثر من نسخة من المكتبة بواسطة إضافات مختلفة. كما أنه يحمّل فقط أحدث إصدار من نفسه (عبر التحقق من الإصدارات المسجلة بعد تحميل جميع الإضافات على خطاف `'plugins_loaded'`).

لاستخدامه في إضافتك (أو قالبك)، ما عليك سوى تضمين ملف `action-scheduler/action-scheduler.php`. سيتولى Action Scheduler بقية الأمر. __ملاحظة:__ لا يُحمَّل Action Scheduler من القالب إلا إذا لم يكن مُضمَّنًا في أي إضافة نشطة.

### لا أريد استخدام WP-Cron. هل يعتمد Action Scheduler على WP-Cron؟

افتراضيًا، يُبدأ تشغيل Action Scheduler بواسطة WP-Cron (وخطاف `'shutdown'` عند طلبات لوحة الإدارة). ومع ذلك، لا يوجد لديه أي اعتماد على نظام WP-Cron. يمكنك بدء صف انتظار Action Scheduler بطرق أخرى بسطر أو سطرين فقط من الشيفرة البرمجية.

على سبيل المثال، يمكنك بدء الصف مباشرة باستدعاء:

```php
ActionScheduler::runner()->run();
```

أو تشغيل خطاف `'action_scheduler_run_queue'` والسماح لـ Action Scheduler بالقيام بذلك نيابةً عنك:

```php
do_action( 'action_scheduler_run_queue', $context_identifier );
```

يمكن إجراء مزيد من التخصيص عبر توسيع فئة `ActionScheduler_Abstract_QueueRunner` لإنشاء مُشغِّل صف مخصص. للاطلاع على مثال لمُشغِّل صف مخصص، راجع [`ActionScheduler_WPCLI_QueueRunner`](https://github.com/woocommerce/action-scheduler/blob/master/classes/WP_CLI/ActionScheduler_WPCLI_QueueRunner.php)، الذي يُستخدم عند تشغيل WP CLI.

هل تريد إنشاء طريقة أخرى لبدء Action Scheduler؟ [افتح مشكلة جديدة](https://github.com/woocommerce/action-scheduler/issues/new)، يسعدنا مساعدتك في ذلك.

### لا أريد استخدام WP-Cron أبدًا. هل يحل Action Scheduler محل WP-Cron؟

افتراضيًا، صُمِّم Action Scheduler ليعمل جنبًا إلى جنب مع WP-Cron دون تغيير أي من سلوكه. يساعد هذا في تجنب تجاوز WP-Cron بشكل غير متوقع على المواقع التي تُثبِّت إضافتك، والتي قد لا تكون لها أي علاقة بـ WP-Cron.

ومع ذلك، نتفهم لماذا قد ترغب في استبدال WP-Cron بالكامل في بيئات تحت سيطرتك، خاصة أن ذلك يمنحك مزايا Action Scheduler. يجب أن يكون هذا ممكنًا دون الكثير من الشيفرة البرمجية.

يمكنك استخدام خطاف `'schedule_event'` في WordPress لاستخدام Action Scheduler فقط لمهام WP-Cron المجدولة حديثًا وربط معامل `$event` بدوال API الخاصة بـ Action Scheduler.

بدلاً من ذلك، يمكنك استخدام مزيج من خطافَي `'pre_update_option_cron'` و `'pre_option_cron'` لتجاوز جميع مهام WP-Cron الجديدة والمجدولة سابقًا (بشكل مشابه لما يفعله [Cavalcade](https://github.com/humanmade/Cavalcade)).

إذا كنت ترغب في إنشاء إضافة للقيام بذلك تلقائيًا ومشاركة عملك مع الآخرين، [افتح مشكلة جديدة لإعلامنا](https://github.com/woocommerce/action-scheduler/issues/new)، يسعدنا مساعدتك في ذلك.

### كيف يخزّن Action Scheduler بياناته؟

يخزّن Action Scheduler 3.0 والأحدث البيانات في جداول مخصصة مسبوقة بـ `actionscheduler_`. للاطلاع على قائمة جميع الجداول ومخططاتها، ارجع إلى فئة `ActionScheduler_StoreSchema`.

قبل الإصدار 3.0، كانت الإجراءات نوع منشور مخصصًا، وكانت البيانات مخزنة في `wp_posts` و `wp_postmeta` والجداول ذات الصلة.

يقوم Action Scheduler 3+ بترحيل البيانات من نوع المنشور المخصص إلى الجداول المخصصة.

### هل يمكنني استخدام مخطط تخزين مختلف؟

بالتأكيد! تخزين بيانات Action Scheduler قابل للاستبدال بالكامل، وكان كذلك دائمًا.

إذا اخترت ذلك، يمكنك تخزينها في أي مكان، مثل خدمة تخزين عن بُعد من Amazon Web Services.

لتنفيذ مخزن مخصص:

1. وسّع الفئة المجردة `ActionScheduler_Store`، مع الحرص على تنفيذ كل طريقة من طرقها
2. أرفق دالة استدعاء راجع بخطاف `'action_scheduler_store_class'` لإخبار Action Scheduler بأن فئتك هي التي يجب استخدامها لإدارة التخزين، مثلاً:

```
function eg_define_custom_store( $existing_storage_class ) {
	return 'My_Radical_Action_Scheduler_Store';
}
add_filter( 'action_scheduler_store_class', 'eg_define_custom_store', 10, 1 );
```

ألقِ نظرة على فئة `classes/data-stores/ActionScheduler_DBStore.php` كمثال على تنفيذ `ActionScheduler_Store`.

إذا كنت ترغب في إنشاء إضافة للقيام بذلك تلقائيًا وإصدارها للعموم لمساعدة الآخرين، [افتح مشكلة جديدة لإعلامنا](https://github.com/woocommerce/action-scheduler/issues/new)، يسعدنا مساعدتك في ذلك.

### هل يمكنني استخدام مخطط تخزين مختلف للتسجيل فقط؟

بالتأكيد! مسجّل Action Scheduler قابل للاستبدال بالكامل، وكان كذلك دائمًا. يمكنك أيضًا تخصيص مكان تخزين السجلات وآلية التخزين.

لتنفيذ مسجّل مخصص:

1. وسّع الفئة المجردة `ActionScheduler_Logger`، مع الحرص على تنفيذ كل طريقة من طرقها
2. أرفق دالة استدعاء راجع بخطاف `'action_scheduler_logger_class'` لإخبار Action Scheduler بأن فئتك هي التي يجب استخدامها لإدارة التسجيل، مثلاً:

```
function eg_define_custom_logger( $existing_storage_class ) {
	return 'My_Radical_Action_Scheduler_Logger';
}
add_filter( 'action_scheduler_logger_class', 'eg_define_custom_logger', 10, 1 );
```

ألقِ نظرة على فئة `classes/data-stores/ActionScheduler_DBLogger.php` كمثال على تنفيذ `ActionScheduler_Logger`.

### أريد تشغيل Action Scheduler فقط على خادم تطبيقات مخصص في مجموعتي. هل يمكنني فعل ذلك؟

نعم، أنت تطرح أسئلة صعبة حقًا. نظريًا، نعم، هذا ممكن. فئة `ActionScheduler_QueueRunner`، المسؤولة عن تشغيل الصفوف، قابلة للاستبدال عبر مرشح `'action_scheduler_queue_runner_class'`.

بفضل ذلك، يمكنك تخصيص تشغيل الصف بالطريقة التي تحتاجها. سواء كان ذلك يعني تعديلات بسيطة، مثل عدم استخدام WP-Cron إطلاقًا لبدء الصفوف عبر تجاوز `ActionScheduler_QueueRunner::init()`، أو تغيير كيفية ومكان تشغيل الصفوف بالكامل عبر تجاوز `ActionScheduler_QueueRunner::run()`.

### هل Action Scheduler آمن للاستخدام على موقعي الإنتاجي؟

نعم، بالتأكيد! يُستخدم Action Scheduler بالفعل على عشرات الآلاف من المواقع الإنتاجية. وهو حاليًا مسؤول عن جدولة كل شيء من رسائل البريد الإلكتروني إلى المدفوعات.

في الواقع، يعالج Action Scheduler كل شهر ملايين المدفوعات كجزء من إضافة [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/).

لا يتطلب أي إعداد، ولن يتجاوز أي واجهات API خاصة بـ WordPress (إلا إذا أردت ذلك).

### كيف يعمل Action Scheduler على WordPress Multisite؟

صُمِّم Action Scheduler لإدارة الإجراءات المجدولة على موقع واحد. ليس لديه معالجة خاصة لتشغيل الصفوف عبر مواقع متعددة في شبكة متعددة المواقع. ومع ذلك، نظرًا لأن تخزينه ومُشغِّل الصف قابلان للاستبدال بالكامل، فمن الممكن كتابة فئات معالجة متعددة المواقع لاستخدامها معه.

إذا كنت ترغب في إنشاء إضافة متعددة المواقع للقيام بذلك وإصدارها للعموم لمساعدة الآخرين، [افتح مشكلة جديدة لإعلامنا](https://github.com/woocommerce/action-scheduler/issues/new)، يسعدنا مساعدتك في ذلك.

### كيف يمكنني تغيير User-Agent الخاص بـ Action Scheduler لتحديد طلباته بشكل أفضل؟

يوفر Action Scheduler مرشحًا باسم `as_async_request_queue_runner_post_args` يمكن استخدامه لتصفية الوسيطات المُرسَلة إلى استدعاء `wp_remote_post`.

معامل User-Agent هو واحد منها فقط ويمكن تعديله كالتالي:

```
function eg_define_custom_user_agent( $args ) {
	$versions           = ActionScheduler_Versions::instance();
	$args['user-agent'] = 'Action Scheduler/' . $versions->latest_version();

	return $args;
}
add_filter( 'as_async_request_queue_runner_post_args', 'eg_define_custom_user_agent', 10, 1 );
```

<!-- ملاحظات الترجمة: [استُخدم "صف" لـ queue كمصطلح شائع في السياق التقني] | [أُبقي على جميع أسماء الفئات والدوال والخطافات بالإنجليزية] | [kept EN: Action Scheduler — اسم منتج/علامة تجارية] | [kept EN: WP-Cron — اسم نظام WordPress] | [kept EN: WordPress Multisite — اسم ميزة] | [kept EN: WooCommerce Subscriptions — اسم منتج] | [kept EN: Cavalcade — اسم مشروع] | [kept EN: Amazon Web Services — اسم علامة تجارية] | [kept EN: WP CLI — اسم أداة] -->