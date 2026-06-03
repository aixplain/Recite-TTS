# Action Scheduler - قائمة انتظار المهام لـ WordPress [![Build Status](https://travis-ci.org/woocommerce/action-scheduler.png?branch=master)](https://travis-ci.org/woocommerce/action-scheduler) [![codecov](https://codecov.io/gh/woocommerce/action-scheduler/branch/master/graph/badge.svg)](https://codecov.io/gh/woocommerce/action-scheduler)

Action Scheduler هو نظام قائمة انتظار مهام قابل للتوسع وقابل للتتبع، مصمم للمعالجة الخلفية لمجموعات كبيرة من الإجراءات في WordPress. وهو مصمم خصيصًا ليتم توزيعه ضمن إضافات WordPress.

يعمل Action Scheduler عن طريق تشغيل خطّاف إجراء (action hook) ليُنفَّذ في وقت ما في المستقبل. يمكن جدولة كل خطّاف ببيانات فريدة، مما يسمح لدوال الاستدعاء بتنفيذ عمليات على تلك البيانات. كما يمكن جدولة الخطّاف ليُنفَّذ مرة واحدة أو أكثر.

فكّر فيه كامتداد لـ `do_action()` يُضيف إمكانية تأخير الخطّاف وتكراره.

## معالجة خلفية مُختبَرة في بيئات الإنتاج

كل شهر، يُعالج Action Scheduler ملايين المدفوعات لصالح [Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/)، وخطّافات الويب (webhooks) لصالح [WooCommerce](https://wordpress.org/plugins/woocommerce/)، بالإضافة إلى رسائل البريد الإلكتروني وأحداث أخرى لمجموعة متنوعة من الإضافات.

لقد تم استخدامه على مواقع حيّة تُعالج قوائم انتظار تتجاوز 50,000 مهمة، وتُنفّذ عمليات كثيفة الموارد مثل معالجة المدفوعات وإنشاء الطلبات، بمعدل مستدام يزيد عن 10,000 مهمة/ساعة دون التأثير سلبًا على عمليات الموقع الاعتيادية.

يتم كل ذلك على بنية تحتية ومواقع WordPress خارج نطاق تحكم مطوّر الإضافة.

إذا كانت إضافتك تحتاج إلى معالجة خلفية، خاصةً لمجموعات كبيرة من المهام، فإن Action Scheduler يمكنه المساعدة.

## اعرف المزيد

لمعرفة المزيد حول كيفية عمل Action Scheduler وكيفية استخدامه في إضافتك، اطّلع على الوثائق على [ActionScheduler.org](https://actionscheduler.org).

ستجد هناك:

* [دليل الاستخدام](https://actionscheduler.org/usage/): تعليمات حول تثبيت Action Scheduler واستخدامه
* [دليل WP CLI](https://actionscheduler.org/wp-cli/): تعليمات حول تشغيل Action Scheduler على نطاق واسع عبر WP CLI
* [مرجع API](https://actionscheduler.org/api/): دليل مرجعي شامل لجميع دوال API
* [دليل الإدارة](https://actionscheduler.org/admin/): دليل لإدارة الإجراءات المجدولة عبر شاشة الإدارة
* [دليل المعالجة الخلفية على نطاق واسع](https://actionscheduler.org/perf/): تعليمات لتشغيل Action Scheduler على نطاق واسع عبر مُشغِّل قائمة WP Cron الافتراضي

## الاعتمادات

يتم تطوير Action Scheduler وصيانته بواسطة [Automattic](http://automattic.com/) مع إنجاز جزء كبير من التطوير المبكر بواسطة [Flightless](https://flightless.us/).

التعاون أمر رائع. يسعدنا العمل معكم لتحسين Action Scheduler. نرحب بـ [طلبات السحب (Pull Requests)](https://github.com/woocommerce/action-scheduler/pulls).

<!-- ملاحظات الترجمة: kept EN: Action Scheduler — اسم منتج/علامة تجارية | kept EN: WordPress, WooCommerce, Automattic, Flightless — أسماء علامات تجارية | kept EN: WP CLI, WP Cron, API — مصطلحات تقنية معتمدة لعدم الترجمة | kept EN: do_action() — كود برمجي | استخدام "مُشغِّل" لـ Runner حسب المسرد المعتمد | استخدام "دوال" لـ functions حسب المسرد المعتمد -->