// ежесекундно обновляем статистику
setInterval(() => {
    $.get('stat', (data) => {
        $('#completed_task_count').html(data.completed)
        $('#total_task_count').html(data.total)
    })
}, 1000)

/**
 * Вывод нового поручения
 *
 * @param afterId после данного номера
 */
let getNextTask = (afterId) => {
    $.get('task/next', {after: afterId}, (data) => {
        $('#task').html(data)
    })
}

$('#task').on('click', '#next', function () {
    const id = $(this).data('id') // выбирем номер текущего поручения из data-атрибута
    getNextTask(id) // делаем запрос на следующее
})

$('#task').on('click', '#complete', function () {
    const id = $(this).data('id')
    $.post('task/' + id + '/complete', (data) => {
        getNextTask(data.id)
    })
})

getNextTask(0) // выбираем первое поручение