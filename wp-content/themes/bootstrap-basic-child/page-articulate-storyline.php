<?php
/**
 * Template for displaying pages
 * 
 * @package bootstrap-basic
 */

get_header();

/**
 * determine main column size from actived sidebar
 */
$main_column_size = bootstrapBasicGetMainColumnSize();
?> 
				<div class="col-md-<?php echo $main_column_size; ?> content-area" id="main-column">
					<main id="main" class="site-main" role="main">
						<script>
var g_bHtml5Supported = true;
</script>
<!--[if lte IE 9]><script>g_bHtml5Supported = false;</script><![endif]-->

<script type="text/javascript">

// Detect min flash version
var g_bMinFlash = false;

if (navigator.plugins["Shockwave Flash"])
{
	var arrDescription = navigator.plugins["Shockwave Flash"].description.split(" ");
	var nVersion = Number(arrDescription[arrDescription.length - 2]);

	g_bMinFlash = (nVersion >= 10) || isNaN(nVersion);
}
else
{
	try 
	{
		var oActiveX = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.10");
		if (oActiveX)
		{
			g_bMinFlash = true;
		}
	}
	catch (e) {}
}

var g_bLMS = false;
var g_bTinCan = false;
var g_bAOSupport = false;
var g_bWarnOnCommitFail = false;
var g_bUseHtml5 = true && g_bHtml5Supported;
var g_bUseMobilePlayer = true;
var g_biOS = (navigator.userAgent.indexOf("AppleWebKit/") > -1 && navigator.userAgent.indexOf("Mobile/") > -1);
var g_biPad = (navigator.userAgent.indexOf("iPad") > -1);
var g_bAndroid = (navigator.userAgent.indexOf("Android") > -1);

var g_bRedirectAMP = ((g_biOS && g_biPad) || g_bAndroid) && g_bUseMobilePlayer;
var g_bRedirectHTML5 = (g_biOS || !g_bMinFlash) && g_bUseHtml5;

if (g_bRedirectAMP)
{
	// Switch to TinCan for AO Reporting
	if (g_bAOSupport)
	{
		g_bTinCan = true;
	}
	
	if (g_bTinCan)
	{
		var strQuery = "tincan=" + g_bTinCan + "&" + document.location.search.substr(1);
		
		if (g_bAOSupport)
		{
			strQuery = "aosupport=true&" + strQuery;
			g_bAOSupport = false;
		}
		
		location.replace("amplaunch.html#" + strQuery);
	}
	else
	{
		location.replace("amplaunch.html");
	}
}
else if (g_bRedirectHTML5)
{
	var strLocation = "story_html5.html";
	
	if (g_bTinCan)
	{
		strLocation += "?tincan=" + g_bTinCan + "&" + document.location.search.substr(1);
	}
	else if (g_bLMS)
	{
		strLocation += "?lms=1";
		
		if (g_bWarnOnCommitFail)
		{
			strLocation += "&warncommit=1";
		}
	}
	
	location.replace(strLocation);	
}

var g_strContentFolder = "story_content";
var g_bProjector = false;
var g_strSwfFile = "story.swf";
var g_nWidth = 740;
var g_nHeight = 658;
var g_strScale = "noscale";	// noscale | show all
var g_strBrowserSize = "default";	// default, fullscreen, optimal
var g_strBgColor = "#FFFFFF";
var g_strAlign = "middle";
var g_strQuality = "best";
var g_bCaptureRC = false;
var g_strFlashVars = "";
var g_bScrollbars = true;
var g_strWMode = "window"; // transparent | window (use "window" for optimal performance, transparent for webobject support)

if (g_strScale == "show all")
{
	g_bScrollbars = false;
}

</script>

<script SRC="story_content/user.js" TYPE="text/javascript"></script>
<script src="story_content/story.js" type="text/javascript"></script>

</head>

<body style="height: 100%;" onunload="DoOnClose()" onbeforeunload="DoOnClose()">

<script type="text/javascript">

document.bgColor = g_strBgColor;

if (g_bScrollbars)
{
	document.write("<table border=0 cellpadding=0 cellspacing=0 width='100%' height='100%' align=center>");
	document.write("<tr>");
	document.write("<td align=center>");
}

	WriteSwfObject(g_strSwfFile,
		       g_nWidth, 
		       g_nHeight, 
		       g_strScale,
		       g_strAlign, 
		       g_strQuality, 
		       g_strBgColor, 
			   g_bCaptureRC,
			   g_strWMode,
		       g_strFlashVars);

if (g_bScrollbars)
{
	document.write("</td>");
	document.write("</tr>");
	document.write("</table>");
}

ResizeBrowser(g_strBrowserSize);
</script>

<DIV id='divEmail' style="position: absolute; width: 10; height: 10; left: 10; top: 10; visibility:hidden" ></DIV>

<DIV id='divWebObjects'></DIV>
					</main>
				</div>


				<div class="general-footer-cta blockclear">
					<?php dynamic_sidebar('general-footer-cta'); ?>
				</div><!-- /.general-footer-cta -->

				<div class="email-signup">
					<?php dynamic_sidebar('email-signup'); ?>
				</div><!-- /.email-signup -->


<?php get_footer(); ?> 