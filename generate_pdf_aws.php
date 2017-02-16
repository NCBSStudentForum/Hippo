<?php

include_once 'database.php';
include_once 'tohtml.php';

function awsToTex( $aws, $with_picture = true )
{
    foreach( $aws as $key => $value )
        $aws[ $key ] = printableCharsOnly( $value );

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

    $title = __ucwords__( $aws[ 'title' ]);
    $abstract = $aws[ 'abstract' ];

    // Add user image.
    $imagefile = $_SESSION[ 'conf' ]['data']['user_imagedir'] . '/' . 
        $aws['speaker'] . '.png';
    if( ! file_exists( $imagefile ) )
        $imagefile = __DIR__ . '/data/no_image_available.png';

    $speakerImg = '\includegraphics[width=5cm]{' . $imagefile . '}';

    $head = '\begin{tikzpicture}[ every node/.style={rectangle
        ,inner sep=1pt,node distance=5mm,text width=0.65\textwidth} ]';
    $head .= '\node[text width=5cm] (image) at (0,0) {' . $speakerImg . '};';
    $head .= '\node[right=of image] (title) { ' .  '{\LARGE ' . $title . '} };';
    $head .= '\node[below=of title] (author) { ' .  '{' . $speaker . '} };';
    $head .= '\node[above=of title] (data) { ' .
        '{\textbf{' . humanReadableDate( $aws[ 'date' ] ) . ', 4:00pm @ 
            Hapus (LH1)} }};';

    $head .= '\end{tikzpicture}';

    // Do not generate the preamble.
    $tex = array( "\documentclass[]{article}"
        , "\usepackage[margin=20mm,a4paper]{geometry}"
        , "\usepackage[]{graphicx}"
        , "\usepackage[]{amsmath,amssymb}"
        , "\usepackage{tikz}"
        , '\usepackage{fancyhdr}'
        , '\linespread{1.5}'
        , '\pagestyle{fancy}'
        , '\pagenumbering{gobble}'
        , '\lhead{\textbf{Annual Work Seminar}}'
        , '\rhead{National Ceneter for Biological Sciences, Bangalore 
                \\\\ TATA Institute of Fundamental Research, Mumbai}'
        , '\usetikzlibrary{calc,positioning,arrows}'
        //, '\usepackage[T1]{fontenc}'
        , '\usepackage[utf8]{inputenc}'
        , '\usepackage[]{lmodern}'
        , '\begin{document}'
        );

    // Header
    $tex[] = $head;

    $tex[] = '\par';

    // remove html formating before converting to tex.
    file_put_contents( '/tmp/abstract.html', $abstract );
    $cmd = 'python ' . __DIR__ . '/html2other.py';
    $texAbstract = `$cmd /tmp/abstract.html md`;
    if( strlen(trim($texAbstract)) > 10 )
        $abstract = $texAbstract;

    // Title and abstract
    $tex[] = $abstract;

    $extra = '\begin{table}[ht!]';
    $extra .= '\begin{tabular}{ll}';
    $extra .= '\textbf{Supervisors} & ' . implode( ",", $supervisors) . '\\\\';
    $extra .= '\textbf{Thesis Committee Members} & ' . implode( ", ", $tcm ) . '\\\\';
    $extra .= '\end{tabular}';
    $extra .= '\end{table}';

    $tex[] = $extra;
    $tex[] = '\end{document}';
    return implode( "\n", $tex );
}

if( ! array_key_exists( 'speaker', $_GET ) )
{
    echo printInfo( 'Invalid request' );
}
else 
{
    $date = $_GET[ 'date' ];
    $speaker = $_GET[ 'speaker' ];
    $aws = getTableEntry( 'annual_work_seminars', 'date,speaker', $_GET );
    // Check in upcoming aws
    if( ! $aws )
        $aws = getTableEntry( 'upcoming_aws', 'date,speaker', $_GET );

    if( ! $aws )
        echo printInfo( "No AWS found for speaker $speaker and date $date" );
    else
    {
        $teX = awsToTex( $aws );
        $texFileName = __DIR__ . "/data/" . $aws['speaker'] . $aws['date'] . ".tex";
        $pdfFileUrl = __DIR__ . '/data/' . $aws[ 'speaker' ] . $aws['date'] . '.pdf';
        $outdir = __DIR__ . "/data";
        file_put_contents( $texFileName, $teX );
        $res = `pdflatex --output-directory $outdir $texFileName`;

        if( file_exists( $pdfFileUrl ) )
        {
            echo printInfo( "Successfully genered pdf document $pdfFileUrl " );
            goToPage( 'download_pdf.php?filename=' .$pdfFileUrl, 0 );
        }
        else
        {
            echo printWarning( "Failed to genered pdf document" );
            echo printWarning( "Error message " );
            echo "<pre> $res </pre>";
        }

        unlink( $texFileName );
    }
};

echo "<br/>";
echo closePage( );

?>
