<?php
namespace App\Controller;

use App\Entity\StockPrice;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StatisticController extends AbstractController
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/statistic/graphs")
     */
    public function graphAction(Request $request)
    {
        $dt = (new \DateTime())->sub(new \DateInterval('P7D'));
        $prices = $this->em->getRepository(StockPrice::class)
            ->createQueryBuilder('p')
            ->andWhere('p.priceDate > :dt')->setParameter('dt', $dt)
            ->addOrderBy('p.priceDate', 'ASC')
            ->addOrderBy('p.publicOfferingId', 'ASC')
            ->getQuery()->getResult();
        $dates = [];
        $datasets = [];
        $valuesByPoByDate = [];
        /** @var StockPrice $price */
        foreach ($prices as $price)
        {
            if(!in_array($price->getPriceDate()->format('d.m H'), $dates)) {
                $dates[] = $price->getPriceDate()->format('d.m H');
            }
            if(!isset($datasets[$price->getPublicOfferingId()])) {
                $datasets[$price->getPublicOfferingId()] = [
                    'label' => $price->getPublicOffering()->getTitle(),
                    'data' => [],
                    'borderColor' => '#'.$this->random_color(),
                    'borderWidth' => 1
                ];
            }
            if(!isset($valuesByPoByDate[$price->getPublicOfferingId()])) {
                $valuesByPoByDate[$price->getPublicOfferingId()] = [];
            }
            if($price->getOldKarma() < 1) {
                continue;
            }
            $value = $price->getCurrentKarma() - $price->getOldKarma();
            $valuesByPoByDate[$price->getPublicOfferingId()][$price->getPriceDate()->format('d.m H')] = $value;
        }
        foreach ($valuesByPoByDate as $poId => $values) {
            foreach ($dates as $date) {
                if(isset($values[$date])) {
                    $datasets[$poId]['data'][] = $values[$date];
                } else {
                    $datasets[$poId]['data'][] = 0;
                }
            }
        }
        /** @var StockPrice $price */
//        foreach ($prices as $price)
//        {
//            if($price->getOldKarma() < 1) {
//                continue;
//            }
//            $value = $price->getCurrentKarma() - $price->getOldKarma();
//            if(!isset($dates[$price->getPriceDate()->format('d.m H')])) {
//                $dates[$price->getPriceDate()->format('d.m H')] = 1;
//            }
//            if(!isset($datasets[$price->getPublicOfferingId()])) {
//                $datasets[$price->getPublicOfferingId()] = [
//                    'label' => $price->getPublicOffering()->getTitle(),
//                    'data' => [],
////                    'backgroundColor' => 'red',
//                    'borderColor' => '#'.$this->random_color(),
//                    'borderWidth' => 1
//                ];
//            }
//            $datasets[$price->getPublicOfferingId()]['data'][] = $value;
//        }

        return $this->render('statistic/index.html.twig', [
            'dates' => array_values($dates),
            'datasets' => array_values($datasets)
        ]);
    }

    /**
     * @Route("/statistic/prices")
     */
    public function priceAction(Request $request)
    {
        $dt = (new \DateTime())->sub(new \DateInterval('P7D'));
        $prices = $this->em->getRepository(StockPrice::class)
            ->createQueryBuilder('p')
            ->andWhere('p.priceDate > :dt')->setParameter('dt', $dt)
            ->addOrderBy('p.priceDate', 'ASC')
            ->addOrderBy('p.publicOfferingId', 'ASC')
            ->getQuery()->getResult();
        $dates = [];
        $datasets = [];
        $valuesByPoByDate = [];
        /** @var StockPrice $price */
        foreach ($prices as $price)
        {
            if(!in_array($price->getPriceDate()->format('d.m H'), $dates)) {
                $dates[] = $price->getPriceDate()->format('d.m H');
            }
            if(!isset($datasets[$price->getPublicOfferingId()])) {
                $datasets[$price->getPublicOfferingId()] = [
                    'label' => $price->getPublicOffering()->getTitle(),
                    'data' => [],
                    'borderColor' => '#'.$this->random_color(),
                    'borderWidth' => 1
                ];
            }
            if(!isset($valuesByPoByDate[$price->getPublicOfferingId()])) {
                $valuesByPoByDate[$price->getPublicOfferingId()] = [];
            }
            if($price->getOldKarma() < 1) {
                continue;
            }
            $value = $price->getPrice();
            $valuesByPoByDate[$price->getPublicOfferingId()][$price->getPriceDate()->format('d.m H')] = $value;
        }
        foreach ($valuesByPoByDate as $poId => $values) {
            foreach ($dates as $date) {
                if(isset($values[$date])) {
                    $datasets[$poId]['data'][] = $values[$date];
                } else {
                    $datasets[$poId]['data'][] = 0;
                }
            }
        }
        return $this->render('statistic/index.html.twig', [
            'dates' => array_values($dates),
            'datasets' => array_values($datasets)
        ]);
    }

    private function random_color_part() {
        return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
    }

    private function random_color() {
        return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }
}