<?php

/*
 * Форма обратной связи (https://itchief.ru/lessons/php/feedback-form-for-website)
 * Copyright 2016-2020 Alexander Maltsev
 * Licensed under MIT (https://github.com/itchief/feedback-form/blob/master/LICENSE)
 */

header('Content-Type: application/json');

// обработка только ajax запросов (при других запросах завершаем выполнение скрипта)
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
  exit();
}

// обработка данных, посланных только методом POST (при остальных методах завершаем выполнение скрипта)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  exit();
}

/* 1 ЭТАП - НАСТРОЙКА ПЕРЕМЕННЫХ */

const
// IS_CHECK_CAPTCHA = false, // проверять капчу
IS_SEND_MAIL = true, // отправлять письмо получателю
// IS_SEND_MAIL_SENDER = false, // отправлять информационное письмо отправителю
IS_WRITE_LOG = true, // записывать данные в лог
// UPLOAD_NAME = 'uploads', // имя директории для загрузки файлов
// IS_SEND_FILES_IN_BODY = true, // добавить ссылки на файлы в тело письма
// IS_SENS_FILES_AS_ATTACHMENTS = false, // необходимо ли прикреплять файлы к письму
// MAX_FILE_SIZE = 524288, // максимальный размер файла (в байтах)
// ALLOWED_EXTENSIONS = array('jpg', 'jpeg', 'bmp', 'gif', 'png'), // разрешённые расширения файлов
MAIL_FROM = 'axibort-send@yandex.ru', // от какого email будет отправляться письмо
MAIL_FROM_NAME = 'Axibort SMTP', // от какого имени будет отправляться письмо
MAIL_SUBJECT = '', // тема письма
MAIL_ADDRESS = 'hh@axibort.com', // кому необходимо отправить письмо
// MAIL_SUBJECT_CLIENT = 'Ваше сообщение доставлено', // настройки mail для информирования пользователя о доставке сообщения
IS_SENDING_MAIL_VIA_SMTP = true, // выполнять отправку писем через SMTP
MAIL_SMTP_HOST = 'ssl://smtp.yandex.ru', // SMTP-хост
MAIL_SMTP_PORT = '465', // SMTP-порт
MAIL_SMTP_USERNAME = 'axibort-send@yandex.ru', // SMTP-пользователь
MAIL_SMTP_PASSWORD = 'cyatpcjmkcrxglab'; // SMTP-пароль
$uploadPath = dirname(dirname(__FILE__)) . '/' . UPLOAD_NAME . '/'; // директория для хранения загруженных файлов
$startPath = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/';

function log_write($message)
{
  if (IS_WRITE_LOG === false) {
    return;
  }
  $output = date('d.m.Y H:i:s') . PHP_EOL . $message . PHP_EOL . '-------------------------' . PHP_EOL;
  file_put_contents(dirname(dirname(__FILE__)) . '/logs/logs.txt', $output, FILE_APPEND | LOCK_EX);
}

/* 2 ЭТАП - ПОДКЛЮЧЕНИЕ PHPMAILER */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('../phpmailer/src/Exception.php');
require_once('../phpmailer/src/PHPMailer.php');
require_once('../phpmailer/src/SMTP.php');

/* 3 ЭТАП - ОТКРЫТИЕ СЕССИИ И ИНИЦИАЛИЗАЦИЯ ПЕРЕМЕННОЙ ДЛЯ ХРАНЕНИЯ РЕЗУЛЬТАТОВ ОБРАБОТКИ ФОРМЫ */

session_start();
$data['result'] = 'success';

/*  ЭТАП - ВАЛИДАЦИЯ ДАННЫХ (ЗНАЧЕНИЙ ПОЛЕЙ ФОРМЫ) */

// проверка поля phone (оно должно быть обязательно заполнено и иметь длину в диапазоне от 2 до 30 символов)
if (isset($_POST['phone'])) {
  $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING); // защита от XSS
  $phoneLength = mb_strlen($phone, 'UTF-8');
} else {
  $data['phone'] = 'Заполните это поле.';
  $data['result'] = 'error';
  log_write('Не пройдена валидация поля: phone! Оно не заполнено!');
}

/*  ЭТАП - ОТПРАВКА ПИСЬМА ПОЛУЧАТЕЛЮ */
if ($data['result'] == 'success' && IS_SEND_MAIL == true) {
  try {
    // получаем содержимое email шаблона
    $bodyMail = file_get_contents('email.tpl');
    // выполняем замену плейсхолдеров реальными значениями
    $bodyMail = str_replace('%email.phone%', isset($phone) ? $phone : '-', $bodyMail);

    // устанавливаем параметры
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';

    /* Отправка письма по SMTP */
    if (IS_SENDING_MAIL_VIA_SMTP === true) {
      $mail->isSMTP();
      $mail->SMTPAuth = true;
      $mail->Host = MAIL_SMTP_HOST;
      $mail->Port = MAIL_SMTP_PORT;
      $mail->Username = MAIL_SMTP_USERNAME;
      $mail->Password = MAIL_SMTP_PASSWORD;
    }

    $mail->Encoding = 'base64';
    $mail->IsHTML(true);
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->Subject = MAIL_SUBJECT;
    $mail->Body = $bodyMail;

    $emails = explode(',', MAIL_ADDRESS);
    foreach ($emails as $address) {
      $mail->addAddress(trim($address));
    }
    
    // отправляем письмо
    if (!$mail->send()) {
      $data['result'] = 'error';
      log_write('Ошибка при отправке письма: ' . $mail->ErrorInfo);
    }
  } catch (Exception $e) {
    log_write('Ошибка: ' . $e->getMessage());
  }
}


/* ЭТАП - ЗАПИСЫВАЕМ ДАННЫЕ В ЛОГ */
if ($data['result'] == 'success' && IS_WRITE_LOG) {
  $output = 'Телефон: ' . (isset($phone) ? $phone : '-') . PHP_EOL;
  log_write('Письмо успешно отправлено!' . PHP_EOL . $output);
}



/* ФИНАЛЬНЫЙ ЭТАП - ВОЗВРАЩАЕМ РЕЗУЛЬТАТЫ РАБОТЫ В ФОРМАТЕ JSON */
echo json_encode($data);