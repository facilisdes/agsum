// fade out
var timer;
var ajaxRequest;
function showSummary(query, summaryElement) {
    var url = "/agsum.php?query="+query;
    try {
        ajaxRequest = $.ajax(
            {
                type: "GET",
                url: url,
                dataType: "xml",
                success: function (xml) {
                    Wipe(summaryElement);
                    var successful = false;
                    $(xml).find('paragraph').each(function () {
                        successful = true;
                        var paragraph = $(this).text();
                        if (paragraph === null) ShowError(xml);
                        summaryElement.append("<p>" + paragraph + "</p>");
                    });
                    if (successful) {
                        summaryElement.append("<p>Список источников (по количеству заимствований):</p>");
                        var list = "<ol>";
                        $(xml).find('url').each(function () {
                            var link = $(this).text();
                            list+="<li><a target=\"_blank\" href='" + link + "'>" + getDomain(link) + "</a></li>";
                        });
                        list+="</ol>";
                        summaryElement.append(list);
                        summaryElement.append("<br>Теги: ");
                        $(xml).find('tag').each(function () {
                            var tag = $(this).text();
                            summaryElement.append("<span>"+tag+" </span>");
                        });
                        var yandexText = $(xml).find('yandex-found-docs-human').text();
                        summaryElement.append("<p>Построено с использованием API Yandex.XML</p><a target=\"_blank\" href=\"https://yandex.ru\"><img src=\"resources/ya.png\"></a><span id=\"yandex_text\"> "+ yandexText+"</span></div>");
                        Display(summaryElement);
                    }
                    else {
                        ShowError(xml, summaryElement);
                    }
                    endTimer();
                },
                error: function (xml) {
                    //endTimer();
                    //ShowError(xml, summaryElement);
                }
            }
        );
    }
    catch (err)
    {
        alert('qwe');
    }
}

function Wipe(element)
{
    element.text("");
}

function Display(element)
{
    element.fadeTo(500, 0.95);
}

function Hide(element)
{
    element.fadeTo(500, 0);
}

function startTimer()
{
    var time = 0;
    timer = setInterval(function()
    {
        time++;
        var timestr = time.toString();
        if(timestr.length===1) timestr = '00' + timestr;
        if(timestr.length===2) timestr = '0' + timestr;
        var pos = timestr.length - 2;
        var text = timestr.slice(0, pos) + ':' + timestr.slice(pos);
        $("#timer").text(text);
    },10);
}

function endTimer()
{
    clearInterval(timer);
}

function getDomain(link)
{
    var r = /:\/\/(.[^/]+)/;
    return link.match(r)[1];
}

function ShowError(xml, summaryElement) {
    Wipe(summaryElement);
    var message = 'Ошибка загрузки. ';
    var error = $(xml).find('message').text();
    message+=error;
    summaryElement.append("<p>" + message + "</p>");
    Display(summaryElement);
}

function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function Summarize(query, summaryElement, isOutputFilled)
{
    if(isOutputFilled) Hide(summaryElement);
    try {
        ajaxRequest.abort();
    }
    catch(e) { };
    endTimer();
    startTimer();
    showSummary(query, summaryElement);
    return true;
}

$(document).ready(function(){

    $('#search_input').focus();
    var summaryElement = $("#summaryContent");
    var isOutputFilled = false;
    $('#search_input').keypress(function(e) {
        if(e.which == 13) {
            var query = escapeHtml($("#search_input").val());
            if(query.length>0)
            {
                isOutputFilled = Summarize(query, summaryElement, isOutputFilled);
            }
        }
    });
    $('#search_button').click(function(e) {
        var query = escapeHtml($("#search_input").val());
        Search(query, summaryElement, isOutputFilled);
        var query = escapeHtml($("#search_input").val());
        if(query.length>0)
        {
            isOutputFilled = Summarize(query, summaryElement, isOutputFilled);
        }
    });
});

function Search(query, summaryElement, isOutputFilled)
{
    return Summarize(query, summaryElement, isOutputFilled);
}

$(document).on('click', '.fbIcon', function(){
    DeselectButton($(this).parent().children());
    SelectButton($(this));
    if($('.fbIconSelected').length==2)
    {
        $('#feedbackReport').show(500);
    }
});

function DeselectButton(element)
{
    element.addClass('fbIcon').removeClass('fbIconSelected');
}

function SelectButton(element)
{
    element.addClass('fbIconSelected').removeClass('fbIcon');
}

$(document).on('click', '.feedbackReportButton', function(){
    $('#feedback2').show(500);
    setTimeout(function(){$('#feedback').hide(750);}, 2000);
    var data = GetSelectedButtons();
    WriteFeedback(data);
});

function GetSelectedButtons()
{
    var result = {};
    var feedback = {};
    $('.fbIconSelected').each(function(i, obj)
    {
        var variant = $(obj).attr('variant_id');
        var question = $(obj).parent().attr('question_id');
        feedback[question] = variant;
    });
    result.feedback = feedback;
    var query = escapeHtml($("#search_input").val());
    result.feedback.query=query;
    return result;
}

function WriteFeedback(data)
{
    $.ajax(
        {
            type:"POST",
            url:'/feedback.php',
            data: data
        }
    );
}

