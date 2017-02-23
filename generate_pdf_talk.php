<?php

include_once 'database.php';
include_once 'tohtml.php';


function eventToTex( $event )
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

    $speaker = __ucwords__( loginToText( $event[ 'speaker' ] , false ));
    $title = __ucwords__( $event[ 'title' ]);
    $abstract = $event[ 'abstract' ];

    // Add user image.
    $imagefile = getSpeakerPicturePath( $event[ 'speaker' ] );

    if( ! file_exists( $imagefile ) )
        $imagefile = nullPicPath( );

    $speakerImg = '\includegraphics[width=5cm]{' . $imagefile . '}';

    // Date and plate
    $dateAndPlace =  humanReadableDate( $event[ 'date' ] ) .  
            ', 4:00pm at \textbf{Hapus (LH1)}';
    $dateAndPlace = '\faCalendarCheckO \quad ' . $dateAndPlace;

    $head = '\begin{tikzpicture}[ every node/.style={rectangle
        ,inner sep=1pt,node distance=5mm,text width=0.65\textwidth} ]';
    $head .= '\node[text width=5cm] (image) at (0,0) {' . $speakerImg . '};';
    $head .= '\node[above right=of image] (schedule)  {\hfill ' . 
                $dateAndPlace . '};';
    $head .= '\node[right=of image] (title) { ' .  '{\LARGE ' . $title . '} };';
    $head .= '\node[below=of title] (author) { ' .  '{' . $speaker . '} };';
    $head .= '\end{tikzpicture}';



    // Header
    $tex = array( $head );
    $tex[] = '\par';

    // remove html formating before converting to tex.
    file_put_contents( '/tmp/abstract.html', $abstract );
    $cmd = 'python ' . __DIR__ . '/html2other.py';
    $texAbstract = `$cmd /tmp/abstract.html tex`;

    if( strlen(trim($texAbstract)) > 10 )
        $abstract = $texAbstract;

    // Title and abstract
    $tex[] = '{\large ' . $abstract . '}';
    $extra = '\begin{table}[ht!]';
    $extra .= '\begin{tabular}{ll}';
    $extra .= '\end{tabular}';
    $extra .= '\end{table}';

    $tex[] = $extra;
    return implode( "\n", $tex );
} // Function ends.

if( array_key_exists( 'date', $_GET ) )
{
    // Get all ids on this day.
    $date = $_GET[ 'date' ];
    $ids = getTableEntries( 'events', '', "date='$date' 
            AND external_id LIKE 'talks.%'" );
}
else if( array_key_exists( 'id', $_GET ) )
    $ids = array( $_GET[ 'id' ] );
else
{
    echo alertUser( 'Invalid request.' );
    exit;
}

// Intialize pdf template.
$tex = array( "\documentclass[]{article}"
    , "\usepackage[margin=20mm,top=3cm,a4paper]{geometry}"
    , "\usepackage[]{graphicx}"
    , "\usepackage[]{amsmath,amssymb}"
    , "\usepackage[]{color}"
    , "\usepackage{tikz}"
    // Old version may not work.
    , "\usepackage{fontawesome}"
    , '\usepackage{fancyhdr}'
    , '\linespread{1.2}'
    , '\pagestyle{fancy}'
    , '\pagenumbering{gobble}'
    , '\lhead{\textsc{{\color{blue} TALK}}}' // This should be changed for each page.
    , '\rhead{National Center for Biological Sciences, Bangalore \\\\ 
        TATA Institute of Fundamental Research, Mumbai}'
    , '\usetikzlibrary{calc,positioning,arrows}'
    //, '\usepackage[T1]{fontenc}'
    , '\usepackage[utf8]{inputenc}'
    , '\usepackage[]{lmodern}'
    , '\begin{document}'
    );

$outfile = 'TALKS_' . $date;

foreach( $ids as $id )
{
    $talk = getTableEntry( 'talks', 'id', array( 'id' => $id ) );
    $event = getEventsOfTalkId( $id );

    $outfile .= '_' . $event[ 'speaker' ];
    $tex[] = eventToTex( $event );
    $tex[] = '\pagebreak';
}

$tex[] = '\end{document}';
$TeX = implode( "\n", $tex );

echo "<pre> $TeX </pre>";

// Generate PDF now.
$outdir = __DIR__ . "/data";
$texFile = $outdir . '/' . $outfile . ".tex";
$pdfFile = $outdir . '/' . $outfile . ".pdf";

file_put_contents( $texFile,  $TeX );
if( file_exists( $texFile ) )
    $res = `pdflatex --output-directory $outdir $texFile`;

if( file_exists( $pdfFile ) )
{
    echo printInfo( "Successfully genered pdf document " . 
       basename( $pdfFile ) );
    goToPage( 'download_pdf.php?filename=' .$pdfFile, 0 );
}
else
{
    echo printWarning( "Failed to genered pdf document <br>
        This is usually due to hidden special characters 
        in your abstract. You need to clean your entry up." );
    echo printWarning( "Error message <small>This is only for diagnostic
        purpose. Show it to someone who is good with LaTeX </small>" );
    echo "<pre> $res </pre>";
}

unlink( $texFile );

echo "<br/>";
echo closePage( );

?>
