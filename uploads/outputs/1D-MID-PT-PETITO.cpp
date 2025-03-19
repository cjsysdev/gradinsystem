#include <stdio.h>
#include <string.h>

#define BASE_FARE_PER_KM 2.5  // Fare per km for regular passengers(form tagbilaran)
struct Stop {
    char name[20];
    int distance; 
};

// List of place to go (from Tagbilaran)
struct Stop stops[] = {
    {"Tagbilaran", 0}, {"Dauis", 5}, {"Panglao", 18}, {"Baclayon", 7}, {"Alburquerque", 14},
    {"Loay", 21}, {"Loboc", 29}, {"Bilar", 42}, {"Carmen", 59}, {"Batuan", 72},
    {"sierra bullones", 85}, {"Pilar", 97}, {"Alicia", 110}, {"Ubay", 125}
};

int totalStops = sizeof(stops) / sizeof(stops[0]);
void displayDestinations() {
    printf("\n==== Ceres Bus Ticketing====\n");
    printf("Asa ni Ma'am/Sir:\n");
    for (int i = 1; i < totalStops; i++) {
        printf("%d. %s (%d km)\n", i, stops[i].name, stops[i].distance);
    }
    printf("0. Exit\n");
}
float getFare(int distance, int type) {
    float fare = distance * BASE_FARE_PER_KM;

    if (type == 2) fare *= 0.80;  // Senior - 20% discount
    else if (type == 3) fare *= 0.90;  // Student (SP) - 15% discount

    return fare;
}
void displayReceipt(int destination, int type, float fare, float payment, float change) {
    printf("\n=== Ticket Receipt ===\n");
    printf("Destination: %s\n", stops[destination].name);
    printf("Passenger Type: %s\n", (type == 1) ? "Regular" : (type == 2) ? "Senior Citizen" : "Student (SP)");
    printf("Fare: ₱%.2f\n", fare);
    printf("Payment: ₱%.2f\n", payment);
    printf("Change: ₱%.2f\n", change);
    printf("Okay sunod Wana kinsay walay ticket diraa,if naana tanan type 0 to exit!\n");
}
void processTicket() {
    int destination, type;
    float fare, payment, change;
 while (1) {
        displayDestinations();
        printf("Enter choice: ");
        scanf("%d", &destination);

        if (destination == 0) {
            printf("Thank you sa pag sakay sa Ceres !\n");
            break;
        }
        if (destination < 1 || destination >= totalStops) {
            printf("Invalid destination. Try again.\n");
            continue;
        }

        printf("\nUnsa ni Sir/Ma'am type:\n");
        printf("1. Regular\n2. Senior Citizen (20%% discount)\n3. Student (SP - 15%% discount)\n");
        printf("Enter type (1-3): ");
        scanf("%d", &type);

        if (type < 1 || type > 3) {
            printf("Invalid type! Try again.\n");
            continue;
        }

        fare = getFare(stops[destination].distance, type);
        printf("\nTotal Fare to %s: ₱%.2f\n", stops[destination].name, fare);

        while (1) {
            printf("Enter payment amount: ₱");
            scanf("%f", &payment);

            if (payment < fare) {
                printf("Insufficient payment! Try again.\n");
            } else {
                change = payment - fare;
                displayReceipt(destination, type, fare, payment, change);
                break;
            }
        }
    }
}
int main() {
    processTicket();
    return 0;
}
    