<?php

namespace App;

class Constant
{

    public const QUOTE_POLICY = [
        "BusinessObjectId" => 949596,
        "@type" => "Policy-POLICY",
        "ProductId" => 949539,
        "ProductName" => "Private Car",
        "BusinessSource" => "1",
        "SubChannel" => "2",
        "SiCurrencyCode" => "SGD",
        "SiLocalExchangeRate" => 1,
        "PremiumCurrencyCode" => "SGD",
        "PremiumLocalExchangeRate" => 1,
        "GSTType" => "Standard Rate",
        "GSTTAX" => 0.07,
        "TypeofInsurance" => 1
    ]; 
 
    public const PRINT_POLICY = [
        "DocCategory" => "NB",
        "DocumentType" => "13,16,17,51,117",
        "IsOnlinePrint" => "Y",
        "NumberOfCopies" => "POLICYHOLDER",
        "ProductCode" => "MPC",
        "ProductId" => 949539,
        "TaskCategory" => ""
    ];
 
    public const SAVE_POLICY = [
        "PolicyWording" => "20719001",
        "ChannelCode" => "A000038",
        "RiskCategoryFn" => "MT",
        "ChannelName" => "AETNA INSURANCE BROKERS PTE LTD (CR LIMIT)",
        "ChannelType" => 6,
        "AgentId" => 20530980,
        "AgreementCode" => "A000038",
        "AgreementId" => 15018635,
        "ChannelGSTRegistrationNo" => "200410774E",
        "ChannelCountry" => "SG",
        "ChannelLeewayDiscount" => 0,
        "CommissionCampaignCode" => "CAMP 2020",
        "CommissionVoucherNo" => "CAMP 2020 FOR MTR",
        "DiscountCode" => "CAMP 2020",
        "VoucherNo" => "CAMP 2020 FOR MTR",
        "CampaignDiscountReleaseType" => 1,
        "CampaignDiscountRate" => 0.03,
        "PolicyLobList" => [
            [
                "BusinessObjectId" => 949595,
                "@type" => "PolicyLob-MPC",
                "SequenceNumber" => "001",
                "PolicyRiskList" => [
                    [
                        "BusinessObjectId" => 949598,
                        "@type" => "PolicyRisk-VEHICLE",
                        "SequenceNumber" => "001",
                        "PolicyPlanList" => [
                            [
                                "BusinessObjectId" => 950616,
                                "@type" => "PolicyPlan-COMP_P",
                                "SequenceNumber" => "001",
                                "TempPolicyCoverageList" => [
                                    [
                                        "BusinessObjectId" => 951359,
                                        "@type" => "PolicyCoverage-COMP",
                                        "IsAddSIToRiskLevel" => "Y",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "MainCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000",
                                        "SumInsured" => "9999999"
                                    ],
                                    [
                                        "BusinessObjectId" => 951417,
                                        "@type" => "PolicyCoverage-VEHICLEREPAIRS",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951466,
                                        "@type" => "PolicyCoverage-TOWING",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951516,
                                        "@type" => "PolicyCoverage-MEDICALEXPENSES",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951566,
                                        "@type" => "PolicyCoverage-WINDSCREEN",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951616,
                                        "@type" => "PolicyCoverage-LOSSORDAMAGE",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951665,
                                        "@type" => "PolicyCoverage-DAMAGETOTPP",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951715,
                                        "@type" => "PolicyCoverage-DEATHORINJURY",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951764,
                                        "@type" => "PolicyCoverage-SRCC",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951813,
                                        "@type" => "PolicyCoverage-INCLUSION",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 951862,
                                        "@type" => "PolicyCoverage-LEGALLIABILITY",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ]
                                ],
                                "IsSelectedNCDCT" => "N"
                            ],
                            [
                                "BusinessObjectId" => 952116,
                                "@type" => "PolicyPlan-TPFT_P",
                                "SequenceNumber" => "001",
                                "TempPolicyCoverageList" => [
                                    [
                                        "BusinessObjectId" => 952315,
                                        "@type" => "PolicyCoverage-TPFT",
                                        "IsAddSIToRiskLevel" => "Y",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "MainCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000",
                                        "SumInsured" => "9999999"
                                    ],
                                    [
                                        "BusinessObjectId" => 952368,
                                        "@type" => "PolicyCoverage-LOSSORDAMAGE",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 952417,
                                        "@type" => "PolicyCoverage-DAMAGETOTPP",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 952467,
                                        "@type" => "PolicyCoverage-DEATHORINJURY",
                                        
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ]
                                ]
                            ],
                            [
                                "BusinessObjectId" => 952517,
                                "@type" => "PolicyPlan-TPO_P",
                                "SequenceNumber" => "001",
                                "TempPolicyCoverageList" => [
                                    [
                                        "BusinessObjectId" => 952667,
                                        "@type" => "PolicyCoverage-TPO",
                                        
                                        "IsAddSIToRiskLevel" => "Y",
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "MainCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000",
                                        "SumInsured" => "9999999"
                                    ],
                                    [
                                        "BusinessObjectId" => 952719,
                                        "@type" => "PolicyCoverage-DAMAGETOTPP",
                                        
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ],
                                    [
                                        "BusinessObjectId" => 952769,
                                        "@type" => "PolicyCoverage-DEATHORINJURY",
                                        
                                        "SequenceNumber" => "001",
                                        "FinalPremium" => "8888888888888",
                                        "isAdjustedPremium" => "Y",
                                        "CoverageCategory" => "SubCT",
                                        "isDel" => "N",
                                        "GrossPremium" => "000000"
                                    ]
                                ]
                            ]
                        ],
                        "NCDSourceCd" => "EX",
                        "QuestionText_6" => "N",
                        // Consider Company
                        "VehUseCd" => "Own Use",
                        "ReferalIndicator" => "N",
                        "PlanCode" => "COMP_P",
                        "PlanName" => "Comprehensive"
                    ]
                ]
            ]
        ],
        "PolicyCommissionRateList" => [
            [
                "BusinessObjectId" => 316589,
                "@type" => "PolicyCommissionRate-PolicyCommissionRate",
                "Rate" => 0.15,
                "RiskCat" => 1,
                "SequenceNumber" => "001",
                "AdjustedCommissionRate" => 0.15,
                "GSTRate" => 0.07,
                "GSTType" => "Standard Rate",
            ]
        ]
    ];
}

?>