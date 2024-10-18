<!DOCTYPE html><html><head>
    <meta charset="UTF-8" />
    <meta name="robots" content="noindex,nofollow" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title>500 Internal Server Error</title>
    <style>
        *{padding: 0;margin: 0}
        body{font-family: Menlo, Monaco, Consolas, "Courier New", monospace;text-align: center;background:#f3f3f3}
        a{text-decoration: underline}

        /* Base */
        .wrap{width:98%; max-width:1100px;margin:0 auto;padding:15px 0;overflow:hidden;font-size:14px;text-align: left}
        .detail {}
        .layout:after{content:'\20';display:block;height:0;clear:both;}
        .layout{*zoom:1;}

        /* Warning */
        .warntop{color:#fff;cursor:pointer;border-radius:3px;}
        .warntop .message{padding:7px 0 5px 0;margin:0 15px;font-weight:bold;}
        .warntop .message a{color:#fff}
        .warntop .fileline{padding-bottom:8px;margin:0 15px;color:#FFD599;font-size:12px}

        .warning{margin-bottom:10px;}
        .warning .warntop{background:#F58B35;}
        .warning .warntop:hover{background:#F4964A;}
        .warning .block{display: none}

        .warningOpen{margin-bottom:10px;}
        .warningOpen .warntop{background:#F4964A;border-radius:3px 3px 0 0;}
        .warningOpen .block{display: block}

        /* Error */
        .error {margin-bottom:10px;}
        .error .top{padding:10px 15px 15px 15px;border-radius:3px 3px 0 0;background:#D9534F;color:#fff;}
        .error .top h2{color:#eee}
        .error .top .message{margin-top:8px;}
        .error .top .message a{color:yellow}
        .error .top .fileline{margin-top:8px;color:#F7AEAB;font-size:12px}

        /*Block*/
        .block{border:1px solid #DCD0D0;border-top:0;border-radius:0 0 3px 3px;overflow:hidden}

        /* Trace */
        .trace{background:#fff;border-top:1px solid #eee;padding:10px 0;border-radius:0 0 4px 4px;color:#444}
        .trace table{border-collapse: collapse;border-spacing: 0;}
        .trace td{padding:5px 10px;font-size:12px;vertical-align:top}
        .trace .number{
            width:1%; white-space:nowrap;border-right:1px solid #eee;
            color:#BFB7B7;text-align: right;
            -webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;
        }
        .trace span{color:#999}
        .trace .file{display:inline;float:right;}
        .trace abbr{text-decoration:none;border-bottom:1px dotted #aaa;cursor:default;}
        .trace abbr.g{color:#999; cursor:pointer}
        .trace abbr.f{cursor:pointer}
        .trace .head,.trace .head span,.trace .head .file{color:#BB2424}

        /*Code*/
        .code{width:100%;font-size:12px;background:#fff;padding:10px 0;overflow-x:auto}
        .code .path{border-bottom:1px solid #e8e8e8;padding:10px;margin-bottom:10px}
        .code table{border-collapse:collapse;border-spacing:0;}
        .code td{padding:5px 10px;vertical-align:top}
        .code .number{
            width:1%;white-space:nowrap;border-right:1px solid #eee;
            color:#BFB7B7;text-align:right;user-select: none;
        }
        .code .inner{white-space:pre;word-wrap:normal;font-size:12px;color:#aaa;}
        .code .errorNumber{background:#F6E6F6;border-color:#DACBDA !important;color:#DF81A4}
        .code .errorInner{background:#F6E6F6;color:#C33C3C}
        .detail .code{
            border-radius:5px;margin-top:10px;padding-top:0;
            background:#f7f7f7;border:1px solid #DEDEDE;color:#666;
        }
        .detail .code .number{border-right:1px solid #ddd;}

        /* Args */
        .args{
            padding:10px;border-radius:5px;margin-top:10px;
            background:#f7f7f7;border:1px solid #DEDEDE;color:#666; overflow-x:auto;
        }
        .args a{color:#666}
        .args em{line-height:21px;font-style:normal;color:#999}

        /* Tip */
        .tinytip{position:absolute;left:600px;top:290px;z-index:2147483647;opacity:.95;font-size:12px;clear:both}
        .tinytip .tinytip-corner{
            width:11px;height:11px;line-height:1;overflow:hidden;font-size:1px;position:absolute;left:50%;margin-left:-5px;top:-5px;background:#000;
            transform:rotate(-45deg);
            -moz-transform:rotate(-45deg);
            -webkit-transform:rotate(-45deg);
            -o-transform:rotate(-45deg);
        }
        .tinytip-top .tinytip-corner{top:auto;bottom:-5px;}
        .tinytip .tinytip-txt{float:left;background:#000;color:#fff;border-radius:2px;padding:5px 8px;}
    </style>
    <script type="text/javascript">
      (function(win){
        var Tip, TipCorner, TipTxt;
        if(!document.getElementsByClassName && document.querySelectorAll) {
          document.getElementsByClassName = function(className) {
            return this.querySelectorAll("." + className);
          };
          Element.prototype.getElementsByClassName = document.getElementsByClassName;
        }

        function getElementsByClass(cls, node){
          node = node||document;
          if(node.getElementsByClassName) {
            return node.getElementsByClassName(cls);
          }
          var a = [];
          var re = new RegExp('(^| )'+cls+'( |$)');
          var els = node.getElementsByTagName("*");
          for(var i=0,j=els.length; i<j; i++) {
            if(re.test(els[i].className))a.push(els[i]);
          }
          return a;
        }

        function getOffset(elm){
          var Sum = function(elm) {
            var top = 0,
              left = 0;
            while(elm){
              top += elm.offsetTop;
              left += elm.offsetLeft;
              elm = elm.offsetParent;
            }
            return {
              top:top,
              left:left
            }
          };
          var Rect = function(elm){
            var box = elm.getBoundingClientRect(),
              body = document.body,
              docElem = document.documentElement,
              scrollTop = window.pageYOffset||docElem.scrollTop||body.scrollTop,
              scrollLeft = window.pageXOffset||docElem.scrollLeft||body.scrollLeft,
              clientTop = docElem.clientTop||body.clientTop,
              clientLeft = docElem.clientLeft||body.clientLeft;
            return {
              top:Math.round(box.top + scrollTop-clientTop),
              left:Math.round(box.left + scrollLeft - clientLeft)
            };
          };
          return elm.getBoundingClientRect ? Rect(elm) : Sum(elm);
        }

        function parentNode(elm, cls) {
          if (elm.className === cls) {
            return elm;
          }
          return elm.parentNode ? parentNode(elm.parentNode, cls) : null;
        }

        function nextNode(elm){
          if(elm && (elm = elm.nextSibling) ) {
            return elm.nodeType!==1 ? nextNode(elm) : elm;
          }
          return null;
        }

        function supportTransform(){
          var prefixes = 'transform WebkitTransform MozTransform OTransform msTransform'.split(' ');
          var div = document.createElement('div');
          for(var i = 0; i < prefixes.length; i++) {
            if(div && div.style[prefixes[i]] !== undefined) {
              return prefixes[i];
            }
          }
          return false;
        }

        function createTip(elm,title){
          var offset = getOffset(elm);
          offset.width = elm.offsetWidth;
          offset.height = elm.offsetHeight;
          if (!Tip) {
            Tip = document.createElement('div');
            Tip.className = 'tinytip';
            if (supportTransform()) {
              TipCorner = document.createElement('div');
              TipCorner.className = 'tinytip-corner';
              Tip.appendChild(TipCorner);
            }
            TipTxt = document.createElement('div');
            TipTxt.className = 'tinytip-txt';
            Tip.appendChild(TipTxt);
            document.body.insertBefore(Tip,document.body.firstChild);
          }

          Tip.style.left = '0px';
          Tip.style.top = '-10000px';
          TipTxt.innerHTML = title;
          Tip.style.display = '';
          Tip.style.left = Math.max(0, (offset.left + offset.width / 2) - Tip.offsetWidth/2) + 'px';
          Tip.style.top = (offset.top + offset.height + 8) + 'px';
        }

        function showTip(elm,title){
          elm.onmouseover = function(){
            createTip(elm, title);
          };
          elm.onmouseout = function(){
            try {
              Tip.style.display = 'none';
            } catch(e) {}
          };
        }

        function formatArgs(args){
          return args.replace(/\*\*\*([a-zA-Z_0-9.$)(]+)\*\*\*\n/g,"<em>$1:</em> ");
        }

        function load(obj){
          var abbrs = obj.getElementsByTagName('abbr'),
            len = abbrs.length;
          if (!len) {
            return;
          }
          for (var i=0; i<len; i++) {
            (function(index){
              var abbr, method, detail, args, code, argsInfo, fixWidth;
              abbr = abbrs[index];
              method = parentNode(abbr, 'method');
              detail = nextNode(method);
              fixWidth = function () {
                if (detail.style.width) {
                  return ;
                }
                detail.style.width = method.offsetWidth + 'px';
              };
              if (detail) {
                args = (args = detail.getElementsByTagName('pre')) && args.length ? args[0] : null;
                if (args) {
                  args.style.display = 'none';
                }
                code = (code = detail.getElementsByTagName('div')) && code.length ? code[0] : null;
                if (code) {
                  code.style.display = 'none';
                }
              }
              if (abbr.className === 'g') {
                if (args && (argsInfo = abbr.title.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '')) !== '') {
                  args.innerHTML = formatArgs(argsInfo);
                  abbr.onclick = function() {
                    fixWidth();
                    if (args.style.display === 'none') {
                      detail.style.display = args.style.display = '';
                      if (code) {
                        code.style.display = 'none';
                      }
                    } else {
                      detail.style.display = args.style.display = 'none';
                    }
                  }
                }
              } else {
                var title = abbr.title||'';
                abbr.title = '';
                showTip(abbr, title);
                if (abbr.className === 'f' && code) {
                  abbr.onclick = function() {
                    fixWidth();
                    if (code.style.display === 'none') {
                      detail.style.display = code.style.display = '';
                      if (args) {
                        args.style.display = 'none';
                      }
                    } else {
                      detail.style.display = code.style.display = 'none';
                    }
                  }
                }
              }
            })(i);
          }
        }

        function trace(){
          var obj = getElementsByClass('trace'),
            len = obj.length,
            i;
          for (i= 0; i < len; i++) {
            load(obj[i]);
          }
        }

        function warning() {
          var obj = getElementsByClass('warning'),
            len = obj.length,
            i;
          for (i= 0; i < len; i++) {
            (function(index){
              var warn = obj[index],
                top = warn.children[0];
              top.onclick = function(){
                warn.className = warn.className==='warning' ? 'warningOpen'  : 'warning';
              };
            })(i);
          }
        }

        win.onload = function(){
          //try {
          warning();
          trace();
          //} catch(e) {}
        }
      })(window);
    </script>
</head><body><div class="wrap">

<?php
use Tanbolt\Debug\DebugUtils;
use Tanbolt\Debug\DebugInterface;

// 输出代码片段
if (!function_exists('_outputErrorPartCode'))
{
    function _outputErrorPartCode(string $file, int $errorLine, bool $trace = false)
    {
        ?>
        <div class="code">
            <?= ($trace ? '<div class="path">'.$file.'</div>' : '') ?>
            <table>
                <?php foreach (DebugUtils::code($file, $errorLine) as $line => $code) {
                    ?>
                    <tr>
                        <td class="number<?= ($errorLine === $line ? ' errorNumber' : '') ?>"><?= $line ?></td>
                        <td class="inner<?= ($errorLine === $line ? ' errorInner' : '') ?>"><?= $code ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <?php
    }
}

// 输出 exception 异常跟踪
if (!function_exists('_outputExceptionBlockHtml'))
{
    function _outputExceptionBlockHtml(Throwable $e)
    {
        $e = DebugUtils::format($e);
        ?>
        <div class="trace">
            <table>
                <tr class="head">
                    <td class="number">#</td>
                    <td>
                        <div class="method">
                            <div class="file">File : Line</div>
                            <span>Class -> Method</span>
                        </div>
                    </td>
                </tr>
                <?php foreach ($e->getTrace() as $key => $trace) {
                    $argsBasic = $argsDetail = '';
                    if ($trace['args']) {
                        $argsBasic = implode(', ', $trace['argsBasic']);
                        if (strlen($argsBasic) > 36) {
                            $argsBasic = substr($argsBasic, 0, 36) . '...';
                        }
                        foreach ($trace['argsDetail'] as $k => $v) {
                            $argsDetail .= '***' . $trace['argsBasic'][$k] . "***\n" . htmlspecialchars($v) . "\n";
                        }
                    }
                    ?>
                    <tr>
                        <td class="number"><?=($key + 1)?></td>
                        <td>
                            <div class="method">
                                <div class="file">
                                    <?php if($trace['file']): ?>
                                        <abbr class="f" title="<?= $trace['file'] ?>">
                                            <?= $trace['fileShort'] ?>
                                        </abbr> : <?= $trace['line'] ?>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </div>
                                <?php if($trace['function']): ?>
                                    <?php if($trace['class']): ?>
                                        <abbr title="<?= $trace['class'] ?>"><?= $trace['classShort'] ?></abbr>
                                        <span><?= $trace['type'] ?></span>
                                    <?php endif; ?>
                                        <?= $trace['function'] ?>
                                        (<abbr title="<?= $argsDetail ?>" class="g"><?= $argsBasic ?></abbr>)
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </div>
                            <div class="detail" style="display:none">
                                <pre class="args"></pre>
                                <?php if ($trace['file']) {
                                    _outputErrorPartCode($trace['file'], $trace['line'], true);
                                } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <?php
    }
}

/**
 * 警告异常: 数组形式 可能有多个 可能为空数组
 * @var DebugInterface $debug
 */
foreach ($debug->getWarnings() as $e)
{
    ?>
    <div class="warning">
        <div class="warntop">
            <div class="message"><?= $e->getMessage() ?></div>
            <div class="fileline"><?= $e->getFile() ?> : <?= $e->getLine() ?></div>
        </div>
        <div class="block">
            <?php
            _outputErrorPartCode($e->getFile(), $e->getLine());
            _outputExceptionBlockHtml($e);
            ?>
        </div>
    </div>
    <?php
}

/**
 * 致命异常: 可能不存在 ( null )
 * @var DebugInterface $debug
 */
if ($e = $debug->getError())
{
    ?>
    <div class="error">
        <div class="top">
            <h2><?= DebugUtils::getClassName($e) ?></h2>
            <div class="message"><?= $e->getMessage() ?></div>
            <div class="fileline"><?= $e->getFile() ?> : <?= $e->getLine() ?></div>
        </div>
        <div class="block">
            <?php
            _outputErrorPartCode($e->getFile(), $e->getLine());
            _outputExceptionBlockHtml($e);
            ?>
        </div>
    </div>
    <?php
}
?>

</div></body></html>