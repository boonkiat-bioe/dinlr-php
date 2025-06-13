<?php
namespace Nava\Dinlr\Models;

/**
 * Customer model with enhanced business logic
 */
class Customer extends AbstractModel
{
    /**
     * Get the customer ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the customer reference
     *
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->getAttribute('reference');
    }

    /**
     * Get the customer first name
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->getAttribute('first_name');
    }

    /**
     * Get the customer last name
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->getAttribute('last_name');
    }

    /**
     * Get the customer full name
     *
     * @return string
     */
    public function getFullName(): string
    {
        $firstName = $this->getFirstName() ?? '';
        $lastName  = $this->getLastName() ?? '';

        return trim("{$firstName} {$lastName}");
    }

    /**
     * Get the customer email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getAttribute('email');
    }

    /**
     * Get the customer phone
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->getAttribute('phone');
    }

    /**
     * Get the company name
     *
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->getAttribute('company_name');
    }

    /**
     * Get the date of birth
     *
     * @return string|null
     */
    public function getDateOfBirth(): ?string
    {
        return $this->getAttribute('dob');
    }

    /**
     * Get the gender
     *
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->getAttribute('gender');
    }

    /**
     * Get address line 1
     *
     * @return string|null
     */
    public function getAddress1(): ?string
    {
        return $this->getAttribute('address1');
    }

    /**
     * Get address line 2
     *
     * @return string|null
     */
    public function getAddress2(): ?string
    {
        return $this->getAttribute('address2');
    }

    /**
     * Get the city
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->getAttribute('city');
    }

    /**
     * Get the country
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->getAttribute('country');
    }

    /**
     * Get the postal code
     *
     * @return string|null
     */
    public function getPostal(): ?string
    {
        return $this->getAttribute('postal');
    }

    /**
     * Get customer notes
     *
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->getAttribute('notes');
    }

    /**
     * Get the customer group ID
     *
     * @return string|null
     */
    public function getCustomerGroupId(): ?string
    {
        return $this->getAttribute('customer_group');
    }

    /**
     * Get the updated at timestamp
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    // Enhanced Business Logic Methods

    /**
     * Check if customer has complete profile information
     *
     * @return bool True if customer has complete information
     */
    public function hasCompleteProfile(): bool
    {
        $requiredFields = ['first_name', 'last_name', 'email', 'phone'];

        foreach ($requiredFields as $field) {
            if (empty($this->getAttribute($field))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get customer's marketing preferences
     *
     * @return array Marketing consent preferences
     */
    public function getMarketingPreferences(): array
    {
        return [
            'email' => (bool) $this->getAttribute('marketing_consent_email', false),
            'text'  => (bool) $this->getAttribute('marketing_consent_text', false),
            'phone' => (bool) $this->getAttribute('marketing_consent_phone', false),
        ];
    }

    /**
     * Check if customer can receive marketing communications
     *
     * @param string $channel Channel to check (email, text, phone)
     * @return bool True if customer consented to marketing for this channel
     */
    public function canReceiveMarketing(string $channel): bool
    {
        $preferences = $this->getMarketingPreferences();
        return $preferences[$channel] ?? false;
    }

    /**
     * Get customer's full address
     *
     * @return string Formatted address string
     */
    public function getFullAddress(): string
    {
        $addressParts = array_filter([
            $this->getAddress1(),
            $this->getAddress2(),
            $this->getCity(),
            $this->getCountry(),
            $this->getPostal(),
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Calculate customer age from date of birth
     *
     * @return int|null Age in years, null if DOB not provided
     */
    public function getAge(): ?int
    {
        $dob = $this->getDateOfBirth();

        if (! $dob) {
            return null;
        }

        $dobDate = \DateTime::createFromFormat('Y-m-d', $dob);
        if (! $dobDate) {
            return null;
        }

        $now = new \DateTime();
        return $now->diff($dobDate)->y;
    }

    /**
     * Check if customer is in a specific age range
     *
     * @param int $minAge Minimum age
     * @param int $maxAge Maximum age
     * @return bool|null True if in range, false if not, null if age unknown
     */
    public function isInAgeRange(int $minAge, int $maxAge): ?bool
    {
        $age = $this->getAge();

        if (null === $age) {
            return null;
        }

        return $age >= $minAge && $age <= $maxAge;
    }

    /**
     * Get customer display name (preferred format)
     *
     * @return string Display name for customer
     */
    public function getDisplayName(): string
    {
        $fullName = $this->getFullName();

        if (! empty($fullName)) {
            return $fullName;
        }

        if ($this->getEmail()) {
            return $this->getEmail();
        }

        if ($this->getReference()) {
            return $this->getReference();
        }

        return 'Customer #' . $this->getId();
    }

    /**
     * Check if customer is male
     *
     * @return bool|null True if male, false if female, null if unknown
     */
    public function isMale(): ?bool
    {
        $gender = $this->getGender();
        return $gender ? ('M' === $gender) : null;
    }

    /**
     * Check if customer is female
     *
     * @return bool|null True if female, false if male, null if unknown
     */
    public function isFemale(): ?bool
    {
        $gender = $this->getGender();
        return $gender ? ('F' === $gender) : null;
    }

    /**
     * Check if customer has address information
     *
     * @return bool True if has at least address1
     */
    public function hasAddress(): bool
    {
        return ! empty($this->getAddress1());
    }

    /**
     * Check if customer has contact information
     *
     * @return bool True if has email or phone
     */
    public function hasContactInfo(): bool
    {
        return ! empty($this->getEmail()) || ! empty($this->getPhone());
    }

    /**
     * Get all marketing consent statuses
     *
     * @return array All consent statuses
     */
    public function getAllConsents(): array
    {
        return [
            'email_consent' => (bool) $this->getAttribute('marketing_consent_email', false),
            'text_consent'  => (bool) $this->getAttribute('marketing_consent_text', false),
            'phone_consent' => (bool) $this->getAttribute('marketing_consent_phone', false),
        ];
    }

    /**
     * Check if customer has any marketing consents
     *
     * @return bool True if customer consented to any marketing channel
     */
    public function hasAnyMarketingConsent(): bool
    {
        $preferences = $this->getMarketingPreferences();
        return in_array(true, array_values($preferences), true);
    }

    /**
     * Convert customer data to array for API operations
     *
     * @return array Customer data array
     */
    public function toApiArray(): array
    {
        $data = [];

        $fields = [
            'reference', 'first_name', 'last_name', 'company_name',
            'email', 'phone', 'dob', 'gender', 'address1', 'address2',
            'city', 'country', 'postal', 'notes', 'marketing_consent_email',
            'marketing_consent_text', 'marketing_consent_phone', 'customer_group',
        ];

        foreach ($fields as $field) {
            $value = $this->getAttribute($field);
            if (null !== $value) {
                $data[$field] = $value;
            }
        }

        return $data;
    }

    public function toArray(): array
    {
        return $this->attributes;   
    }

    /**
     * Get customer summary for display
     *
     * @return array Summary information
     */
    public function getSummary(): array
    {
        return [
            'id'                 => $this->getId(),
            'display_name'       => $this->getDisplayName(),
            'email'              => $this->getEmail(),
            'phone'              => $this->getPhone(),
            'complete_profile'   => $this->hasCompleteProfile(),
            'marketing_consents' => count(array_filter($this->getMarketingPreferences())),
            'updated_at'         => $this->getUpdatedAt(),
        ];
    }
}
