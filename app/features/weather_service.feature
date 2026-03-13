Feature: Weather service
    In order to provide weather information
    As an API consumer
    I want to receive temperature and trend for a city

    Scenario: Positive trend is returned
        Given the provider has weather data for "Sofia" with current 4 and average 1
        When I request the weather for "Sofia"
        Then the returned temperature should be 4
        And the returned suffix should be "🥵"

    Scenario: Negative trend is returned
        Given the provider has weather data for "Varna" with current 2 and average 5
        When I request the weather for "Varna"
        Then the returned temperature should be 2
        And the returned suffix should be "🥶"

    Scenario: Repeated requests within the cache window do not call the provider twice
        Given the provider has weather data for "Plovdiv" with current 7 and average 7
        When I request the weather for "Plovdiv" twice
        Then the provider should be called 1 time for "Plovdiv"
