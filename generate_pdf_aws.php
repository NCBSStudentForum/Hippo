<?php

include_once 'database.php';
include_once 'tohtml.php';


function awsToTex( $aws )
{
    // First sanities the html before it can be converted to pdf.
    foreach( $aws as $key => $value )
    {
        // See this 
        // http://stackoverflow.com/questions/9870974/replace-nbsp-characters-that-are-hidden-in-text
        $value = htmlentities( $value, null, 'utf-8' );
        $value = str_replace( '&nbsp;', '', $value );
        $value = preg_replace( '/\s+/', ' ', $value );
        $value = html_entity_decode( trim( $value ) );
        $aws[ $key ] = $value;
    }

    $speaker = __ucwords__( loginToText( $aws[ 'speaker' ] , false ));

    $supervisors = array( __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'supervisor_1' ] ), false ))
                ,  __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'supervisor_2' ] ), false ))
            );
    $supervisors = array_filter( $supervisors );

    $tcm = array( );
    array_push( $tcm, __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_1' ] ), false ))
            , __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_2' ] ), false ))
            ,  __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_3' ] ), false ))
            , __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_4' ] ), false ))
        );
    $tcm = array_filter( $tcm );

    $title = $aws[ 'title' ];
    $abstract = $aws[ 'abstract' ];

    // Add user image.
    $imagefile = $_SESSION[ 'conf' ]['data']['user_imagedir'] . '/' . 
        $aws['speaker'] . '.png';
    if( ! file_exists( $imagefile ) )
        $imagefile = __DIR__ . '/data/null.png';

    $speakerImg = '\includegraphics[width=5cm]{' . $imagefile . '}';

    // Date and plate
    $dateAndPlace =  humanReadableDate( $aws[ 'date' ] ) .  
            ', 4:00pm \faHome \, \textbf{Haapus (LH1), ELC, NCBS}';
    $dateAndPlace = '\faClockO \, ' . $dateAndPlace;

    $head = '\begin{tikzpicture}[ every node/.style={rectangle
        ,inner sep=1pt,node distance=5mm,text width=0.65\textwidth} ]';
    $head .= '\node[text width=5cm] (image) at (0,0) {' . $speakerImg . '};';
    $head .= '\node[above right=of image] (schedule)  {\hfill ' . 
                $dateAndPlace . '};';
    $head .= '\node[right=of image] (title) { ' .  '\textsc{\LARGE ' . $title . '} };';
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
    $extra .= '\textbf{Supervisor(s)} & ' . implode( ",", $supervisors) . '\\\\';
    $extra .= '\textbf{Thesis Committee Member(s)} & ' . implode( ", ", $tcm ) . '\\\\';
    $extra .= '\end{tabular}';
    $extra .= '\end{table}';

    $tex[] = $extra;
    return implode( "\n", $tex );
} // Function ends.

if( ! array_key_exists( 'date', $_GET ) )
{
    echo printInfo( 'Invalid request' );
    echo closePage( );
    exit;
}
else 
{
    $date = $_GET[ 'date' ];
    $whereExpr = "date='" . $date . "'";
    if( array_key_exists( 'speaker', $_GET ) )
        $whereExpr .= " AND speaker='" . $_GET[ 'speaker' ] . "'";

    $awses = getTableEntries( 'annual_work_seminars', '', $whereExpr );
    $upcomingS = getTableEntries( 'upcoming_aws', '', $whereExpr ); 
    $awses = array_merge( $awses, $upcomingS );
}

// Intialize pdf template.
$tex = array( "\documentclass[]{article}"
    , "\usepackage[margin=20mm,top=3cm,a4paper]{geometry}"
    , "\usepackage[]{graphicx}"
    , "\usepackage[]{amsmath,amssymb}"
    , "\usepackage[]{color}"
    , "\usepackage{tikz}"
    , "\usepackage{fontawesome}"
    , '\usepackage{fancyhdr}'
    , '\linespread{1.2}'
    , '\pagestyle{fancy}'
    , '\pagenumbering{gobble}'
    , '\lhead{\textsc{{\color{blue} Annual Work Seminar}}}'
    , '\rhead{National Center for Biological Sciences, Bangalore \\\\ 
        TATA Institute of Fundamental Research, Mumbai}'
    , '\usetikzlibrary{calc,positioning,arrows}'
    , '\usepackage[]{ebgaramond}'
    , '\usepackage[T1]{fontenc}'
    , '\begin{document}'
    );

$outfile = 'AWS_' . $date;
foreach( $awses as $aws )
{
    $outfile .= '_' . $aws[ 'speaker' ];
    $tex[] = awsToTex( $aws );
    $tex[] = '\pagebreak';
}

$tex[] = '\end{document}';
$TeX = implode( "\n", $tex );
//echo "<pre> $TeX </pre>";

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
