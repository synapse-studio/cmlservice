# Выгрузка товаров из 1с в Drupal 8
*NB - это стартовый модуль для разработчиков. <br>
Задачи:
- отладка обмена
- основа для создания продакшен-решений
- понять структуру обмена

## Модуль служит для интеграции 1С и Друпал8 по протоколу CommerceML

Модуль обмена 1С и Drupal 8.<br />
На каждый обмен создаётся нода cml, которая содержит в себе файлы:
- import.xml - структуру ткаталога и товары
- offers.xml - предложения по товарам (цена и количество)

Прилетевшие картинки кладутся в папку files<br>
Список всех обменов доступен на странице /cml<br>

Адрес для обмена: http://***.n2.s1dev.ru/cmlservice/1c <br />
Страница настроек: /admin/config/system/cmlservice

<img src="https://github.com/politsin/help/blob/master/1csett.png?raw=true">


cd /var/www/html && \
composer update && \
composer config repositories.drupal composer https://packages.drupal.org/8 && \
drush en -y commerce_product && \
git clone https://github.com/synapse-studio/tovar /var/www/html/modules/custom/features/tovar && \
git clone -b 8.x-3.x --single-branch https://github.com/drupalprojects/feeds /var/www/html/modules/contrib/feeds && \
cp /var/www/html/modules/custom/cmlservice/config/tpl/field.field.node.tovar.field_tovar_variation.yml /var/www/html/modules/custom/features/tovar/config/install && \
cp /var/www/html/modules/custom/cmlservice/config/tpl/field.storage.node.field_tovar_variation.yml /var/www/html/modules/custom/features/tovar/config/install && \
drush en -y tovar && \
cp /var/www/html/modules/custom/cmlservice/feeds.patch.txt /var/www/html/modules/contrib/feeds && \
cd /var/www/html/modules/contrib/feeds && \
patch -p1 < feeds.patch.txt && \
drush en -y feeds


