<?php

namespace Database\Seeders;

use App\Models\Icd10Code;
use Illuminate\Database\Seeder;

/**
 * Curated starter set of ~100 frequently-used ICD-10 codes covering common
 * OPD presentations. The full WHO ICD-10 catalog is ~14,000 codes — hand
 * seeding all of them is out of scope; this reference set is a practical
 * starting point and can be extended via the standard Icd10Code CRUD.
 */
class Icd10CodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            // Infectious & parasitic
            ['A09', 'Diarrhoea and gastroenteritis of presumed infectious origin', 'Infectious'],
            ['A90', 'Dengue fever', 'Infectious'],
            ['B01.9', 'Varicella without complication', 'Infectious'],
            ['B05.9', 'Measles without complication', 'Infectious'],
            ['B34.9', 'Viral infection, unspecified', 'Infectious'],
            ['B54', 'Malaria, unspecified', 'Infectious'],

            // Respiratory
            ['J00', 'Acute nasopharyngitis (common cold)', 'Respiratory'],
            ['J02.9', 'Acute pharyngitis, unspecified', 'Respiratory'],
            ['J03.9', 'Acute tonsillitis, unspecified', 'Respiratory'],
            ['J06.9', 'Acute upper respiratory infection, unspecified', 'Respiratory'],
            ['J09', 'Influenza due to identified zoonotic or pandemic virus', 'Respiratory'],
            ['J11.1', 'Influenza with other respiratory manifestations', 'Respiratory'],
            ['J18.9', 'Pneumonia, unspecified', 'Respiratory'],
            ['J20.9', 'Acute bronchitis, unspecified', 'Respiratory'],
            ['J30.4', 'Allergic rhinitis, unspecified', 'Respiratory'],
            ['J45.9', 'Asthma, unspecified', 'Respiratory'],
            ['J44.9', 'Chronic obstructive pulmonary disease, unspecified', 'Respiratory'],

            // Cardiovascular
            ['I10', 'Essential (primary) hypertension', 'Cardiovascular'],
            ['I20.9', 'Angina pectoris, unspecified', 'Cardiovascular'],
            ['I25.9', 'Chronic ischaemic heart disease, unspecified', 'Cardiovascular'],
            ['I48.9', 'Atrial fibrillation and flutter, unspecified', 'Cardiovascular'],
            ['I50.9', 'Heart failure, unspecified', 'Cardiovascular'],
            ['I63.9', 'Cerebral infarction, unspecified', 'Cardiovascular'],
            ['I83.9', 'Varicose veins of lower extremities without complications', 'Cardiovascular'],

            // Endocrine / metabolic
            ['E10.9', 'Type 1 diabetes mellitus without complications', 'Endocrine'],
            ['E11.9', 'Type 2 diabetes mellitus without complications', 'Endocrine'],
            ['E03.9', 'Hypothyroidism, unspecified', 'Endocrine'],
            ['E05.9', 'Thyrotoxicosis, unspecified', 'Endocrine'],
            ['E66.9', 'Obesity, unspecified', 'Endocrine'],
            ['E78.5', 'Hyperlipidaemia, unspecified', 'Endocrine'],
            ['E86', 'Volume depletion (dehydration)', 'Endocrine'],

            // Gastrointestinal
            ['K21.9', 'Gastro-oesophageal reflux disease without oesophagitis', 'Gastrointestinal'],
            ['K29.7', 'Gastritis, unspecified', 'Gastrointestinal'],
            ['K30', 'Functional dyspepsia', 'Gastrointestinal'],
            ['K52.9', 'Noninfective gastroenteritis and colitis, unspecified', 'Gastrointestinal'],
            ['K59.0', 'Constipation', 'Gastrointestinal'],
            ['K59.1', 'Functional diarrhoea', 'Gastrointestinal'],
            ['K35.80', 'Acute appendicitis, unspecified', 'Gastrointestinal'],
            ['K80.20', 'Calculus of gallbladder without cholecystitis', 'Gastrointestinal'],
            ['K92.2', 'Gastrointestinal haemorrhage, unspecified', 'Gastrointestinal'],

            // Musculoskeletal
            ['M25.50', 'Pain in unspecified joint', 'Musculoskeletal'],
            ['M54.5', 'Low back pain', 'Musculoskeletal'],
            ['M54.2', 'Cervicalgia (neck pain)', 'Musculoskeletal'],
            ['M17.9', 'Osteoarthritis of knee, unspecified', 'Musculoskeletal'],
            ['M79.1', 'Myalgia', 'Musculoskeletal'],
            ['M06.9', 'Rheumatoid arthritis, unspecified', 'Musculoskeletal'],
            ['M81.0', 'Postmenopausal osteoporosis', 'Musculoskeletal'],

            // Genitourinary
            ['N39.0', 'Urinary tract infection, site not specified', 'Genitourinary'],
            ['N30.9', 'Cystitis, unspecified', 'Genitourinary'],
            ['N18.9', 'Chronic kidney disease, unspecified', 'Genitourinary'],
            ['N20.0', 'Calculus of kidney', 'Genitourinary'],
            ['N40', 'Benign prostatic hyperplasia', 'Genitourinary'],
            ['N92.6', 'Irregular menstruation, unspecified', 'Genitourinary'],

            // Dermatological
            ['L20.9', 'Atopic dermatitis, unspecified', 'Dermatological'],
            ['L30.9', 'Dermatitis, unspecified', 'Dermatological'],
            ['L50.9', 'Urticaria, unspecified', 'Dermatological'],
            ['L03.90', 'Cellulitis, unspecified', 'Dermatological'],
            ['B35.9', 'Dermatophytosis, unspecified (fungal skin infection)', 'Dermatological'],
            ['L70.0', 'Acne vulgaris', 'Dermatological'],

            // Neurological
            ['G43.909', 'Migraine, unspecified, not intractable', 'Neurological'],
            ['G44.209', 'Tension-type headache, unspecified, not intractable', 'Neurological'],
            ['G40.909', 'Epilepsy, unspecified, not intractable', 'Neurological'],
            ['G45.9', 'Transient cerebral ischaemic attack, unspecified', 'Neurological'],
            ['R51', 'Headache', 'Neurological'],

            // Mental health
            ['F32.9', 'Major depressive disorder, single episode, unspecified', 'Mental Health'],
            ['F41.9', 'Anxiety disorder, unspecified', 'Mental Health'],
            ['F51.0', 'Insomnia not due to a substance or known physiological condition', 'Mental Health'],
            ['F43.10', 'Post-traumatic stress disorder, unspecified', 'Mental Health'],

            // Eye / ENT
            ['H10.9', 'Conjunctivitis, unspecified', 'Eye'],
            ['H66.90', 'Otitis media, unspecified', 'ENT'],
            ['H61.20', 'Impacted cerumen, unspecified ear', 'ENT'],
            ['J32.9', 'Chronic sinusitis, unspecified', 'ENT'],

            // Obstetric / gynaecological
            ['O26.9', 'Pregnancy-related condition, unspecified', 'Obstetric'],
            ['Z34.9', 'Encounter for supervision of normal pregnancy, unspecified', 'Obstetric'],
            ['N91.2', 'Amenorrhoea, unspecified', 'Gynaecological'],

            // Pediatric
            ['P59.9', 'Neonatal jaundice, unspecified', 'Pediatric'],
            ['R62.50', 'Unspecified lack of expected normal physiological development in childhood', 'Pediatric'],

            // Injury / trauma
            ['S01.90', 'Unspecified open wound of head', 'Injury'],
            ['S60.90', 'Unspecified injury of wrist, hand and fingers', 'Injury'],
            ['S93.40', 'Sprain of ankle, unspecified', 'Injury'],
            ['T14.90', 'Injury, unspecified', 'Injury'],
            ['T78.40', 'Allergy, unspecified', 'Injury'],

            // General signs & symptoms
            ['R05', 'Cough', 'General'],
            ['R06.02', 'Shortness of breath', 'General'],
            ['R07.9', 'Chest pain, unspecified', 'General'],
            ['R10.9', 'Abdominal pain, unspecified', 'General'],
            ['R11.0', 'Nausea', 'General'],
            ['R11.10', 'Vomiting, unspecified', 'General'],
            ['R42', 'Dizziness and giddiness', 'General'],
            ['R50.9', 'Fever, unspecified', 'General'],
            ['R53.83', 'Other fatigue', 'General'],
            ['R60.9', 'Oedema, unspecified', 'General'],

            // Follow-up / administrative
            ['Z00.00', 'General adult medical examination without abnormal findings', 'Administrative'],
            ['Z09', 'Follow-up examination after treatment for conditions other than malignant neoplasm', 'Administrative'],
            ['Z71.3', 'Dietary counselling and surveillance', 'Administrative'],
        ];

        foreach ($codes as [$code, $description, $category]) {
            Icd10Code::query()->updateOrCreate(
                ['code' => $code],
                ['description' => $description, 'category' => $category, 'is_billable' => true, 'status' => 1],
            );
        }
    }
}
