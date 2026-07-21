# تشغيل الموقع على كمبيوتر محلي مع مزامنة للسيرفر

## الفكرة
- نسخة الكمبيوتر تعمل من قاعدة بيانات محلية، لذلك الموقع يفتح حتى لو الإنترنت مقطوع.
- عند عودة الإنترنت، ملف `scripts/sync_push_computer.php` يرسل نسخة مضغوطة من قاعدة البيانات إلى السيرفر.
- ملف `api/sync_receive_server.php` على السيرفر يستقبل النسخة ويستوردها، فيصبح الموقع الأونلاين نسخة من الكمبيوتر.

> هذه مزامنة اتجاه واحد: من الكمبيوتر إلى السيرفر. لا تعدّل بيانات السيرفر يدويًا أثناء استخدام النظام المحلي حتى لا تضيع عند أول مزامنة قادمة.

## البرنامج المقترح للكمبيوتر
استخدم **XAMPP for Windows** لأنه يحتوي على Apache + MariaDB/MySQL + PHP + phpMyAdmin في حزمة واحدة سهلة.

## ملفات الكمبيوتر
1. انسخ المشروع داخل `C:\xampp\htdocs\ps`.
2. أنشئ قاعدة بيانات محلية باسم `ps_local` من phpMyAdmin.
3. استورد الملف الأصلي `psxeqwgl_playstation (1).sql` داخل قاعدة `ps_local`.
4. شغّل `database_sync_computer.sql` اختياريًا داخل نفس القاعدة.
5. انسخ `includes/config_computer.php` فوق `includes/config.php` على الكمبيوتر فقط.
6. عدّل `includes/sync_settings_computer.php`:
   - `SYNC_COMPUTER_DB_NAME` = اسم قاعدة الكمبيوتر.
   - `SYNC_COMPUTER_SERVER_URL` = رابط السيرفر الحقيقي، مثل `https://your-domain.com/ps/api/sync_receive_server.php`.
   - `SYNC_COMPUTER_API_KEY` = كلمة سر قوية، ولازم تكون نفس القيمة على السيرفر.

## ملفات السيرفر
1. ارفع المشروع على الاستضافة.
2. أنشئ قاعدة بيانات السيرفر واستورد `psxeqwgl_playstation (1).sql`.
3. شغّل `database_sync_server.sql` اختياريًا.
4. انسخ `includes/config_server.php` فوق `includes/config.php` على السيرفر فقط.
5. عدّل `includes/sync_settings_server.php`:
   - بيانات قاعدة السيرفر.
   - `SYNC_SERVER_API_KEY` = نفس كلمة السر الموجودة في الكمبيوتر.
   - `SYNC_SERVER_ALLOWED_BRANCHES` = `main` أو اسم الفرع/المحل عندك.

## تشغيل السيرفر المحلي على الكمبيوتر
1. نزّل وثبّت XAMPP.
2. افتح XAMPP Control Panel.
3. شغّل Apache و MySQL.
4. افتح `http://localhost/phpmyadmin` وأنشئ قاعدة `ps_local`.
5. افتح الموقع من `http://localhost/ps`.

## تشغيل المزامنة تلقائيًا على Windows
1. افتح Task Scheduler.
2. Create Basic Task باسم `PlayStation Cafe Sync`.
3. اختر Trigger كل 5 دقائق أو كل 10 دقائق.
4. Action: Start a program.
5. Program/script: `C:\xampp\php\php.exe`.
6. Add arguments: `C:\xampp\htdocs\ps\scripts\sync_push_computer.php`.
7. احفظ المهمة. عند انقطاع النت ستفشل المهمة، وعند عودته ستنجح وترفع آخر نسخة.

## اختبار يدوي
من Command Prompt على الكمبيوتر شغّل:

```bat
C:\xampp\php\php.exe C:\xampp\htdocs\ps\scripts\sync_push_computer.php
```

لو ظهرت رسالة `Sync uploaded successfully` يبقى الرفع تم.
