<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', []);
})
->bind('homepage')
;

$app->get('stat', function () use ($app) {
    $sql = 'SELECT COUNT(*) AS count FROM task UNION SELECT COUNT(*) FROM task WHERE status = "completed"';

    $result = $app['db']->fetchAll($sql);
    
    return $app->json([
        'total'     => $result[0]['count'],
        'completed' => isset($result[1]) ? $result[1]['count'] : $result[0]['count'], # с обработкой на случай всех выполненных поручений
    ]);
})
;

$app->get('task/next', function (Request $request) use ($app) {
    $getParams = $request->query->all(); # получаем GET-параметры
    $after = isset($getParams['after']) ? (int) $getParams['after'] : 0;

    $sql = 'SELECT * FROM task WHERE status = "new" AND id > ? LIMIT 1';
    
    $task = $app['db']->fetchAssoc($sql, [$after]);

    return $app['twig']->render('task/item.html.twig', [
        'id'    => $task ? $task['id'] : 0,
        'text'  => $task ? $task['text'] : '',
    ]);
})
;

$app->post('task/{id}/complete', function ($id) use ($app) {
    $sql = 'UPDATE task SET status = "completed" WHERE id = ?';

    $id = $app['db']->executeUpdate($sql, [(int) $id]);
    if (!$id) {
        $app->error(function () { return new Response('Ошибка обновления БД'); });
    }

    return $app->json(['id' => $id]);
})
->assert('id', '\d+') # принимаем только цифры
;


$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
