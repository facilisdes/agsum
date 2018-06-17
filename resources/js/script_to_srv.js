// fade out
var timer;
var ajaxRequest;
function showSummary(summaryElement) {
    var query = escapeHtml($("#search_input").val());
    var url = "/agsum.php?query="+query;
    try {
        ajaxRequest = $.ajax(
            {
                type: "GET",
                url: url,
                dataType: "xml",
                success: function (xml) {
                    var successful = false;
                    $(xml).find('paragraph').each(function () {
                        successful = true;
                        var paragraph = $(this).text();
                        if (paragraph === null) ShowError(xml);
                        $("#summaryContent").append("<p>" + paragraph + "</p>");
                    });
                    if (successful) {
                        summaryElement.append("Список источников (по количеству заимствований):<ul>");
                        $(xml).find('url').each(function () {
                            var link = $(this).text();
                            $("#summaryContent").append("<li><a target=\"_blank\" href='" + link + "'>" + getDomain(link) + "</a></li>");
                        });
                        summaryElement.append("</ul>");
                        summaryElement.append("<br>Теги: ");
                        $(xml).find('tag').each(function () {
                            var tag = $(this).text();
                            $("#summaryContent").append("<span>"+tag+" </span>");
                        });
                        var yandexText = $(xml).find('yandex-found-docs-human').text();
                        $("#summaryContent").append("<p id=\"imagediv\"><p>Построено с использованием API Yandex.XML</p><a target=\"_blank\" href=\"https://yandex.ru\"><img src=\"resources/ya.png\"></a><span id=\"yandex_text\"> "+ yandexText+"</span></div>");
                        summaryElement.show(600);
                    }
                    else {
                        ShowError(xml, summaryElement);
                    }
                    endTimer();
                },
                error: function (xml) {
                    endTimer();
                    ShowError(xml, summaryElement);
                }
            }
        );
    }
    catch (err)
    {
        alert('qwe');
    }
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
    var message = 'Ошибка загрузки. ';
    var error = $(xml).find('message').text();
    message+=error;
    summaryElement.append("<p>" + message + "</p>");
    summaryElement.show(600);
}

function ClearSummary(summaryElement) {
    summaryElement.hide(200);
    summaryElement.text("");
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

function Summarize(summaryElement, isOutputFilled)
{
    try {
        ajaxRequest.abort();
    }
    catch(e) { };
    endTimer();
    startTimer();
    if(isOutputFilled) ClearSummary(summaryElement);
    showSummary(summaryElement);
    return true;
}

$(document).ready(function(){

    var summaryElement = $("#summaryContent");
    var contentElement = $("#pageContent");
    var searchInput = $("#search_input");
    $(".cssload-container").fadeTo(0, 0);
    var isOutputFilled = false;
    var isSearchQueryFilled = false;
    var isFadeLocked = false;
    contentElement.mouseenter(function()
   {
       if(!isFadeLocked & !isSearchQueryFilled && !isOutputFilled) {
           $("#pageContent").fadeTo(200, 1);
       }
   });
    contentElement.mouseleave(function()
    {
        if(!isFadeLocked & !isSearchQueryFilled && !isOutputFilled) {
            $("#pageContent").fadeTo(200, 0.7);
        }
    });
    searchInput.focus(function()
    {
        isFadeLocked = true;
    });
    searchInput.blur(function()
    {
        var query = $("#search_input").val();
        isSearchQueryFilled = query!=="";
        isFadeLocked = false;
    });
    $('#search_input').keypress(function(e) {
        if(e.which == 13) {
            isOutputFilled = Summarize(summaryElement, isOutputFilled);
        }
    });
    $('#search_button').click(function(e) {
        isOutputFilled = Summarize(summaryElement, isOutputFilled);
    });
});