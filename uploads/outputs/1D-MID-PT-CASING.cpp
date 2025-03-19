#include <stdio.h>

void check_balance(float balance) {
    printf("Current GCash Balance: %.2f PHP\n", balance);
}

float cash_in(float balance, float amount) {
    balance += amount; 
    printf("Successfully added %.2f PHP to your GCash account.\n", amount);
    return balance;
}

float cash_out(float balance, float amount) {
    if (amount > balance) {
        printf("Insufficient balance for this cash out. Your balance is only %.2f PHP.\n", balance);
    } else {
        balance -= amount; 
        printf("Successfully withdrew %.2f PHP from your GCash account.\n", amount);
    }
    return balance;
}

int main() {
    float balance = 0.0; 
    int choice;
    float amount;

   
    while (1) {
        printf("\n----- GCash Cash In and Cash Out Simulation -----\n");
        printf("1. Check Balance\n");
        printf("2. Cash In\n");
        printf("3. Cash Out\n");
        printf("4. Exit\n");
        printf("Enter your choice: ");
        scanf("%d", &choice);

        switch (choice) {
            case 1:
                check_balance(balance); 
                break;
            case 2:
                printf("Enter amount to Cash In: ");
                scanf("%f", &amount);
                if (amount <= 0) {
                    printf("Invalid amount! Please enter a positive value.\n");
                } else {
                    balance = cash_in(balance, amount);  // Add money to balance
                }
                break;
            case 3:
                printf("Enter amount to Cash Out: ");
                scanf("%f", &amount);
                if (amount <= 0) {
                    printf("Invalid amount! Please enter a positive value.\n");
                } else {
                    balance = cash_out(balance, amount);  // Subtract money from balance
                }
                break;
            case 4:
                printf("Exiting the program. Goodbye and Thankyou!\n");
                return 0;  // Exit the program
            default:
                printf("Invalid choice. Please try again.\n");
                break;
        }
    }

    return 0;
}