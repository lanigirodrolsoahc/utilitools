<?php

namespace Utilitools;

final
class Period extends HtmlGenerator
{
    use Dates;

    private ?\DateTime $end;
    private array $list = [];
    private ?Locale $locale;
    private ?\DateTime $start;
    private bool $styled = false;
    private array $tree = [];

    public
    function __construct ()
    {
        parent::__construct();

        $this->locale = ( System::Instance()->{ System::LOCALE } ?? French::Instance() );
    }

    /**
     * clears current list of periods
     *
     * @return  Period
     */
    private
    function clear () : Period
    {
        $this->list = [];
        $this->tree = [];

        return $this;
    }

    /**
     * gets days between `$this->start` and `$this->end`
     *
     * @param   Period  &$period
     *
     * @return  array<\DOMElement>
     */
    private
    function daysBetween ( Period &$period ) : array
    {
        do
        {
            $out = [];

            if ( $period->start === null ) break;
            if ( $period->end === null ) break;

            $firster = fn ( \DateTime &$date ) : \DateTime => ( clone $date )
                ->modify('first day of this month')
                ->setTime(0, 0, 0);

            $ploder = fn ( array $data ) : string => implode(
                ' ',
                \array_filter($data)
            );

            $isFirstHalf    = $period->start->format( $fmtTimed = 'H:i:s' ) > ( $noon = '12:00:00' );
            $dayStart       = $period->start->format( $fmtDay = 'd' );
            $ymStart        = $period->start->format( $fmtYm = 'Y-m' );
            $dayStop        = (int) $period->end->format($fmtDay);
            $isLastHalf     = $period->end->format($fmtTimed) <= $noon;
            $ymEnd          = $period->end->format($fmtYm);
            $current        = $firster($period->start);
            $end            = $firster($period->end);

            while ( $current <= $end )
            {
                if (
                    ! \array_key_exists(
                        $year = (int) $current->format( $fmtYear = 'Y' ),
                        $period->tree
                    )
                )
                    $period->tree[$year] = [];

                if ( ! \in_array(
                    $month = (int) $current->format('m'),
                    $period->tree[$year],
                    true
                ) )
                    $period->tree[$year][] = $month;

                foreach (
                    \array_keys(
                        (
                            new WorkingDays(
                                $yeared = (int) $current->format($fmtYear),
                                $monthed = (int) $current->format('m'),
                                \intval(
                                    $ymStart == ( $currentYm = $current->format($fmtYm) )
                                        ? $dayStart
                                        : $current->format($fmtDay)
                                )
                            )
                        )
                            // ->buildWorkingDays()
                            ->buildWorkingDays(false)
                            ->workdays
                    ) as $day
                )
                {
                    if (
                        $currentYm == $ymStart
                        && $day == (int) $dayStart
                        && $isFirstHalf
                    )
                        $classed = 'halfedFirst';
                    elseif (
                        $currentYm == $ymEnd
                        && $day == $dayStop
                        && $isLastHalf
                    )
                        $classed = 'halfedLast';
                    else
                        $classed = null;

                    if (
                        $currentYm == $ymEnd
                        && $day > $dayStop
                    )
                        break 2;

                    $this->__add(
                        $this->__div( (string) $day ),
                        $dayItem = $this->__div()
                    )
                    ->__class( $dayItem, $ploder( ['dayed', $classed] ) )
                    ->__att(
                        $dayItem,
                        'title',
                        $ploder(
                            [
                                $this->locale->fullDate( ( clone $current )->setDate($yeared, $monthed, $day) ),
                                $classed
                                    ? \sprintf('(%1$s)', 'demi-journée')
                                    : null
                            ]
                        )
                    );

                    $out[] = $dayItem;
                }

                $current->modify('+1 month');
            }
        }
        while ( 0 );

        return $out;
    }

    /**
     * fills days element into a period reprensentation
     *
     * @param   Period  &$period
     *
     * @return  \DOMElement
     */
    private
    function fill ( Period &$period ) : \DOMElement
    {
        $this
            ->__add(
                [
                    $title = $this->__div(),
                    $parts = $this->__div()
                ],
                $item = $this->__div()
            )
            ->__add(
                \array_map(
                    fn ( \DateTime $date ) => $this->__div( $this->locale->fullDate($date) ),
                    \array_filter(
                        $period->start instanceof \DateTime
                        && $period->end instanceof \DateTime
                        ? [
                            $period->start,
                            $period->end->format(self::$SIMPLE_DATE_FORMAT)
                            != $period->start->format(self::$SIMPLE_DATE_FORMAT)
                                ? $period->end
                                : false
                        ]
                        : []
                    )
                ),
                $title
            )
            ->__add( $this->daysBetween($period), $parts )
            ->__class($item, 'PeriodItem');

        return $item;
    }

    /**
     * inserts current representation into given document
     *
     * @param   \DOMDocument     &$doc
     *
     * @return  \DOMElement
     */
    public
    function insertableIn ( \DOMDocument &$doc ) : \DOMElement
    {
        return $doc->importNode( $this->represent(), true );
    }

    /**
     * gives a period representation
     *
     * @return  \DOMElement
     */
    private
    function represent () : \DOMElement
    {
        $this
            ->__add(
                \array_map(
                    fn ( Period $period ) : \DOMElement => $this->fill($period),
                    $this->list ?: [ clone $this ]
                ),
                $block = $this->__div()
            )
            ->__class($block, 'Period');

        if ( ! $this->styled )
            $this->styled = ! $this->__style( \sprintf('%1$s/style/Periods.style.css', __DIR__), $block );

        return $block;
    }

    /**
     * determines start date and time
     *
     * @param   ?\DateTime    $start
     *
     * @return  Period
     */
    public
    function starts ( ?\DateTime $start  = null ) : Period
    {
        $this->start = $start;

        return $this;
    }

    /**
     * determines end date and time
     *
     * @param   ?\DateTime    $end
     *
     * @return  Period
     */
    public
    function stops ( ?\DateTime $end = null ) : Period
    {
        $this->end = $end;

        return $this;
    }

    /**
     * stores a period for a later group release
     *
     * @return  Period
     */
    public
    function store () : Period
    {
        $this->list[] = ( clone $this )->clear();

        return $this->starts()->stops();
    }
}
