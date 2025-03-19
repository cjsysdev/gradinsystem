#include <stdio.h>


float balance = 123456789;
int pin[] = {123};
int attempts = 0;
 
// mao ni ang function declaration 
void showMenu();
void withdraw();
void deposit();
void balanceInquiry();
void cancelTransaction();

void showMenu() {
    int choice;
    
    while(1){
    printf("\nWelcome to your account... Please Select Your Transaction:\n");
    printf("\n1. Withdraw\n");
    printf("2. Deposit\n");
    printf("3. Balance Inquiry\n");
    printf("4. Cancel Transaction\n");
    
    printf("\nEnter your choice: ");
    scanf("%d", &choice);

    switch (choice) {
        case 1: withdraw();
         break;
        case 2: deposit();
         break;
        case 3: balanceInquiry(); 
         break;
        case 4: cancelTransaction(); 
         break;
        default: printf("Invalid choice!\n");
    }
}
}
void withdraw() {
    float amount;
    printf("\nEnter the amount you want to withdraw: ");
    scanf("%f", &amount);
    
    if (amount > balance) {
        printf("Insufficient balance!\n");
    } else {
        balance -= amount;
        printf("\nYou have successfully withdrawn %.2f from your account.\n", amount);
        printf("\nYour remaining balance is: %.2f\n", balance);
    }
}

void deposit() {
    float amount;
    printf("\nEnter the amount you want to deposit: ");
    scanf("%f", &amount);
    
    balance += amount;
    printf("\nYou have successfully deposited %.2f into your account.\n", amount);
    printf("\nYour new balance is: %.2f\n", balance);
}

void balanceInquiry() {
    printf("\nYour current balance is: %.2f\n", balance);
}

void cancelTransaction() {
    printf("\nTransaction has been cancelled!\n");
    exit(0);
}

int validatePin(int enteredPin) {
    for (int i = 0; i < 3; i++) {
        if (enteredPin == pin[i]) {
            return 1; // return 1 sija kay mao man ag valid pin
        }
    }
    return 0; // then return 0 if invalid pin, nya 3 times nakang try 
}

int main() {
    int enteredPin;
    
    while (attempts < 3) {
        printf("\nEnter your PIN code: ");
        scanf("%d", &enteredPin);
        
        if (validatePin(enteredPin)) {
            showMenu();
            return 0;
        } else {
            printf("Wrong PIN! Try again.\n");
            attempts++;
        }
    }
    
    printf("Your account has been locked!\n");
    return 0;
}