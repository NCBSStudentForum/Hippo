<?php

include_once 'database.php';
include_once 'tohtml.php';

// This script may also be called by command line by the email bot. To make sure 
// $_GET works whether we call it from command line or browser.
if( isset($argv) )
    parse_str( implode( '&' , array_slice( $argv, 1 )), $_GET );

function eventToTex( $event, $talk = null )
{
    // First sanities the html before it can be converted to pdf.
    foreach( $event as $key => $value )
    {
        // See this 
        // http://stackoverflow.com/questions/9870974/replace-nbsp-characters-that-are-hidden-in-text
        $value = htmlentities( $value, null, 'utf-8' );
        $value = str_replace( '&nbsp;', '', $value );
        $value = preg_replace( '/\s+/', ' ', $value );
        $value = html_entity_decode( trim( $value ) );
        $event[ $key ] = $value;
    }

    // Crate date and plate.
    $where = venueSummary( $event[ 'venue' ] );
    $when = humanReadableDate( $event['date'] ) . ', ' . 
        humanReadableTime( $event[ 'start_time' ] );

    $title = $event[ 'title' ];
    $desc = $event[ 'description' ];
    $speaker = '';

    // Prepare speaker image.
    $imagefile = getSpeakerPicturePath( $talk[ 'speaker' ] );
    if( ! file_exists( $imagefile ) )
        $imagefile = nullPicPath( );

    // Add user image.
    $imagefile = getThumbnail( $imagefile );
    $speakerImg = '\includegraphics[width=5cm]{' . $imagefile . '}';

    if( $talk )
    {
        $title = $talk['title'];
        $desc = fixHTML( $talk[ 'description' ] );

        // Get speaker.
        $speakerHTML = speakerToHTML( $talk['speaker' ] );
        $speakerTex = html2Tex( $speakerHTML );
        $speaker = $speakerTex;
    }

    // Header
    $head = '\begin{tikzpicture}[ every node/.style={rectangle
        ,inner sep=1pt,node distance=5mm,text width=0.65\textwidth} ]';
    $head .= '\node[text width=5cm] (image) at (0,0) {' . $speakerImg . '};';
    $head .= '\node[above right=of image] (when)  { 
                    \hfill \faClockO \,' .  $when . ' \faHome \,' . $where . '};';
    $head .= '\node[below=of when, yshift=0mm] (title) { ' .  '\textsc{\LARGE ' . $title . '} };';
    $head .= '\node[below=of title] (author) { ' .  '{' . $speaker . '} };';
    $head .= '\end{tikzpicture}';
    $tex = array( $head );

    // Put talk class in header.
    if( $talk )
        $tex[ ] = '\lhead{\textsc{\color{blue}' . $talk['class'] . '}}';

    $tex[] = '\par';

    // remove html formating before converting to tex.
    file_put_contents( '/tmp/__event__.html', $desc );
    $cmd = 'python ' . __DIR__ . '/html2other.py';
    $texAbstract = `$cmd /tmp/__event__.html tex`;

    if( strlen(trim($texAbstract)) > 10 )
        $desc = $texAbstract;

    // Extra.
    $tex[] = '{\large ' . $desc . '}';
    if( $talk )
    {
        $extra = '\vspace{1cm}';
        $extra = '\begin{table}[ht!]';
        $extra .= "\begin{tabular}{ll}\n";
        //$extra .= "\\toprule\n";
        $extra .= 'Host & ' . $talk[ 'host' ] . '\\\\';
        if( $talk[ 'coordinator' ] )
            $extra .= 'Coordinator & ' . $talk[ 'coordinator' ] . '\\\\';
        //$extra .= '\bottomrule';
        $extra .= '\end{tabular} \end{table}';
        $tex[] = $extra;
    }
    return implode( "\n", $tex );
} // Function ends.


// Intialize pdf template.
$tex = array( "\documentclass[]{article}"
    , "\usepackage[margin=25mm,top=3cm,a4paper]{geometry}"
    , "\usepackage[]{graphicx}"
    , "\usepackage[]{grffile}"
    //, "\usepackage[]{booktabs}"
    , "\usepackage[]{amsmath,amssymb}"
    , "\usepackage[colorlinks=true]{hyperref}"
    , "\usepackage[]{color}"
    , "\usepackage{tikz}"
    // Old version may not work.
    , "\usepackage{fontawesome}"
    , '\usepackage{fancyhdr}'
    , '\linespread{1.2}'
    , '\pagestyle{fancy}'
    , '\pagenumbering{gobble}'
    , '\rhead{National Center for Biological Sciences, Bangalore \\\\ 
        TATA Institute of Fundamental Research, Mumbai}'
    , '\usetikzlibrary{calc,positioning,arrows}'
    //, '\usepackage[sfdefault,light]{FiraSans}'
    , '\usepackage[]{ebgaramond}'
    , '\usepackage[T1]{fontenc}'
    , '\begin{document}'
    );


$ids = array( );
if( array_key_exists( 'id', $_GET ) )
{
    array_push( $ids, $_GET[ 'id' ] );
}
else if( array_key_exists( 'date', $_GET ) )
{
    // Get all ids on this day.
    $date = $_GET[ 'date' ];
    echo printInfo( "Events on $date" );
    $outfile = 'EVENTS_ON_' . dbDate( $date );
    $entries = getPublicEventsOnThisDay( $date );
    foreach( $entries as $entry )
        array_push( $ids, explode( '.', $entry[ 'external_id' ] )[1] );
}
else
{
    echo alertUser( 'Invalid request.' );
    exit;
}

// Prepare TEX document.
$outfile = 'EVENTS';
foreach( $ids as $id )
{
    echo printInfo( "Generating pdf for id $id" );
    $talk = getTableEntry( 'talks', 'id', array( 'id' => $id ) );
    $event = getEventsOfTalkId( $id );
    $outfile .= '_' . $event[ 'date' ];
    $tex[] = eventToTex( $event, $talk );
    $tex[] = '\pagebreak';
}

$tex[] = '\end{document}';
$TeX = implode( "\n", $tex );

// Generate PDF now.
$outdir = __DIR__ . "/data";
$texFile = $outdir . '/' . $outfile . ".tex";
$pdfFile = $outdir . '/' . $outfile . ".pdf";

file_put_contents( $texFile,  $TeX );
$cmd = "pdflatex --output-directory $outdir $texFile";
if( file_exists( $texFile ) )
    $res = `$cmd`;

if( file_exists( $pdfFile ) )
{
    echo printInfo( "Successfully genered pdf document " . 
       basename( $pdfFile ) );

    // Download only if called from browser.
    if( ! isset( $argv ) )
        goToPage( 'download_pdf.php?filename=' .$pdfFile, 0 );
}
else
{
    echo printWarning( "Failed to genered pdf document <br>
        This is usually due to hidden special characters 
        in your description. You need to clean your entry up." );
    echo printWarning( "Error message <small>This is only for diagnostic
        purpose. Show it to someone who is good with LaTeX </small>" );
    echo "Command <pre> $cmd </pre>";
    echo "<pre> $res </pre>";
}

unlink( $texFile );

echo "<br/>";
echo closePage( );

?>
