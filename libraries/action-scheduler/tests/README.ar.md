# اختبارات Action Scheduler

لتشغيل اختبارات الوحدة:

1. تأكّد من أنّ PHPUnit مُثبَّت عن طريق تشغيل:
    ```
    $ composer install
    ```

2. ثبِّت WordPress ومكتبة اختبارات وحدة WP باستخدام سكريبت `install.sh`:
    ```
    $ tests/bin/install.sh <db-name> <db-user> <db-password> [db-host] [wp-version] [skip-database-creation]
    ```

قد تحتاج إلى وضع السلاسل النصية التي تحتوي على شرطات مائلة عكسية بين علامات اقتباس لمنع معالجتها بواسطة الصدفة (shell) أو البرامج الأخرى.

بعد ذلك، لتشغيل الاختبارات:
    ```
    $ composer run test
    ```

<!-- ملاحظات الترجمة: [kept EN: Action Scheduler — brand/plugin name] | [kept EN: PHPUnit, WordPress, WP — brand/tool names] | [kept EN: composer, install.sh — command/file names] | [translated "unit tests" as "اختبارات الوحدة"] | [translated "shell" with transliteration "الصدفة (shell)" for clarity] -->