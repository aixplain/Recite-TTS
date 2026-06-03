---
description: تعرّف على كيفية استخدام Action Scheduler كطابور معالجة خلفية لمهام WordPress في إضافتك.
---
# الاستخدام

يتطلب استخدام Action Scheduler:

1. تثبيت المكتبة
1. جدولة إجراء

## التثبيت

هناك طريقتان لتثبيت Action Scheduler:

1. كإضافة WordPress عادية؛ أو
1. كمكتبة ضمن قاعدة الشيفرة البرمجية لإضافتك.

### الاستخدام كإضافة

يتضمن Action Scheduler ترويسات الملفات اللازمة لاستخدامه كإضافة WordPress قياسية.

لتثبيته كإضافة:

1. قم بتنزيل أرشيف .zip لأحدث [إصدار مستقر](https://github.com/woocommerce/action-scheduler/releases)
1. انتقل إلى شاشة الإدارة **الإضافات > أضف جديد > رفع** في موقع WordPress الخاص بك
1. حدد ملف الأرشيف الذي قمت بتنزيله للتو
1. انقر على **تثبيت الآن**
1. انقر على **تفعيل**

أو قم باستنساخ مستودع Git إلى مجلد `wp-content/plugins` في موقعك.

يمكن أن يكون استخدام Action Scheduler كإضافة مفيدًا للتطوير مقابل إصدارات أحدث، بدلاً من الاضطرار إلى تحديث الشجرة الفرعية في قاعدة الشيفرة البرمجية الخاصة بك. **عند تثبيته كإضافة، لا يوفر Action Scheduler أي واجهات مستخدم لجدولة الإجراءات**. الطريقة الوحيدة للتفاعل مع Action Scheduler هي عبر الشيفرة البرمجية.

### الاستخدام كمكتبة

لاستخدام Action Scheduler كمكتبة:

1. قم بتضمين قاعدة الشيفرة البرمجية لـ Action Scheduler
1. قم بتحميل المكتبة عبر تضمين ملف `action-scheduler.php`

استخدام [شجرة فرعية في مستودع Git الخاص بإضافتك أو قالبك أو موقعك](https://www.atlassian.com/blog/git/alternatives-to-git-submodule-git-subtree) لتضمين Action Scheduler هو الأسلوب الموصى به. يمكن أيضًا استخدام Composer.

لتضمين Action Scheduler كشجرة فرعية git:

#### خطوة 1. إضافة المستودع كمصدر بعيد

```
git remote add -f subtree-action-scheduler https://github.com/woocommerce/action-scheduler.git
```

إضافة الشجرة الفرعية كمصدر بعيد تتيح لنا الإشارة إليها بشكل مختصر عبر الاسم `subtree-action-scheduler`، بدلاً من عنوان GitHub الكامل.

#### خطوة 2. إضافة المستودع كشجرة فرعية

```
git subtree add --prefix libraries/action-scheduler subtree-action-scheduler master --squash
```

سيُضيف هذا فرع `master` من Action Scheduler إلى مستودعك في المجلد `libraries/action-scheduler`.

يمكنك تغيير `--prefix` لتغيير مكان تضمين الشيفرة البرمجية. أو تغيير فرع `master` إلى وسم، مثل `2.1.0` لتضمين إصدار مستقر فقط.

#### خطوة 3. تحديث الشجرة الفرعية

لتحديث Action Scheduler إلى إصدار جديد، استخدم الأوامر:

```
git fetch subtree-action-scheduler master
git subtree pull --prefix libraries/action-scheduler subtree-action-scheduler master --squash
```

### تحميل Action Scheduler

بغض النظر عن طريقة تثبيته، لتحميل Action Scheduler، تحتاج فقط إلى تضمين ملف `action-scheduler.php`، مثال:

```php
<?php
require_once( plugin_dir_path( __FILE__ ) . '/libraries/action-scheduler/action-scheduler.php' );
```

لا حاجة لاستدعاء أي دوال أو القيام بأي شيء آخر لتهيئة Action Scheduler.

عند تضمين ملف `action-scheduler.php`، سيقوم Action Scheduler بتسجيل الإصدار الموجود في ذلك الملف ثم تحميل أحدث إصدار من نفسه على الموقع. كما سيقوم بتحميل أحدث إصدار من [جميع دوال API](https://actionscheduler.org/api/).

### ترتيب التحميل

سيقوم Action Scheduler بتسجيل إصداره عند `'plugins_loaded'` بأولوية `0` - بعد تحميل جميع قواعد الشيفرة البرمجية للإضافات الأخرى. لذلك **يجب تضمين ملف `action-scheduler.php` قبل `'plugins_loaded'` بأولوية `0`**.

يُوصى بتحميله _عند تضمين الملف الذي يتضمنه_. ومع ذلك، إذا كنت بحاجة إلى تحميله عبر خطاف (hook)، فيجب أن يحدث الخطاف قبل `'plugins_loaded'`، أو يمكنك استخدام `'plugins_loaded'` بأولوية سالبة، مثل `-10`.

سيقوم Action Scheduler لاحقًا بتهيئة نفسه عند `'init'` بأولوية `1`. لا ينبغي استخدام واجهات API الخاصة بـ Action Scheduler إلا بعد `'init'` بأولوية `1`.

### الاستخدام في القوالب

عند استخدام Action Scheduler في القوالب، من المهم ملاحظة أنه إذا تم تسجيل Action Scheduler بواسطة إضافة، فسيتم استخدام أحدث إصدار مسجل بواسطة إضافة، بدلاً من الإصدار المضمن في القالب. وذلك بسبب شيفرة معالجة تبعيات الإصدارات التي تستخدم `'plugins_loaded'` منذ الإصدار 1.0.

## جدولة إجراء

لجدولة إجراء، قم باستدعاء [دالة API](/api/) لنوع الجدولة المطلوب مع تمرير المعاملات المطلوبة.

يوضح المثال أدناه كل ما هو مطلوب لجدولة دالة للتشغيل عند منتصف الليل، إذا لم تكن مجدولة بالفعل:

```php
require_once( plugin_dir_path( __FILE__ ) . '/libraries/action-scheduler/action-scheduler.php' );

/**
 * Schedule an action with the hook 'eg_midnight_log' to run at midnight each day
 * so that our callback is run then.
 */
function eg_schedule_midnight_log() {
	if ( false === as_has_scheduled_action( 'eg_midnight_log' ) ) {
		as_schedule_recurring_action( strtotime( 'tomorrow' ), DAY_IN_SECONDS, 'eg_midnight_log' );
	}
}
add_action( 'init', 'eg_schedule_midnight_log' );

/**
 * A callback to run when the 'eg_midnight_log' scheduled action is run.
 */
function eg_log_action_data() {
	error_log( 'It is just after midnight on ' . date( 'Y-m-d' ) );
}
add_action( 'eg_midnight_log', 'eg_log_action_data' );
```

لاحظ أن الدالة `as_has_scheduled_action()` أُضيفت في الإصدار 3.3.0: إذا كنت تستخدم إصدارًا أقدم، فيجب عليك استخدام `as_next_scheduled_action()` بدلاً منها. لمزيد من التفاصيل حول جميع دوال API المتاحة والبيانات التي تقبلها، راجع [مرجع API](/api/).

<!-- ملاحظات الترجمة: [أُبقي على Action Scheduler كاسم علامة تجارية دون ترجمة] | [أُبقي على WordPress كاسم علامة تجارية] | [أُبقي على أوامر git والشيفرة البرمجية دون ترجمة] | [kept EN: Composer — brand name] | [kept EN: API — اختصار تقني معتمد] | [استُخدم "مستودع" لـ Repository و"مكتبة" لـ Library و"دالة/دوال" لـ Function/Functions و"معاملات" لـ Parameters حسب المسرد المعتمد] -->