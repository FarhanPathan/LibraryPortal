<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Auth;
use App\Models\Account;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class DashboardController extends Controller
{
    //
    public function index(){
        if(empty(auth()->user()->id)){
            return redirect()->route('welcome');
        }
        $books = Book::all();
        return view('dashboard', ['books' => $books]);
    }

    public function borrow(){
        if(empty(auth()->user()->id)){
            return redirect()->route('welcome');
        }
        return view('borrow');
    }

    public function borrow_book(Request $request){
        if(empty(auth()->user()->id)){
            return redirect()->route('welcome');
        }
        $book = Book::where('isbn', $request->isbn)->first();
        $account = Account::where('book_id', $request->isbn)->where('student_id', Auth::user()->student_id)->where('date_returned', null)->first();
        if($account){
            return redirect()->route('myaccount')->with('error', 'You have already borrowed this book');
        }
        if($book){
            if($book->copies > 0){
                $book->copies = $book->copies - 1;
                $book->save();

                $account = new Account();
                $account->student_id = Auth::user()->student_id;
                $account->book_id = $book->isbn;
                $account->date_borrowed = date('Y-m-d');
                $account->due_date = date('Y-m-d', strtotime('+2 days'));
                $account->save();

                return redirect()->route('myaccount')->with('success', 'You have borrowed '.$book->title.' until '.$account->due_date);
            }else{
                return redirect()->back()->with('error', 'Book not available');
            }
        }else{
            return redirect()->back()->with('error', 'Book not found, Invalid ISBN');
        }
    }

    public function return(){
        if(empty(auth()->user()->id)){
            return redirect()->route('welcome');
        }
        return view('return');
    }

    public function return_book(Request $request){
        if(empty(auth()->user()->id)){
            return redirect()->route('welcome');
        }
        $book = Book::where('isbn', $request->isbn)->first();
        if($book){
            //get details from acounts table where book_id = $request->isbn adn student_id = Auth::user()->id
            $account = Account::where('book_id', $request->isbn)->where('student_id', Auth::user()->student_id)->where('date_returned', null)->first();
            if($account){
                //check if today is greater than due date
                if(date('Y-m-d') > $account->due_date){
                    //set difference to overdue
                    $overdue = strtotime(date('Y-m-d')) - strtotime($account->due_date);
                    $overdue = floor($overdue / (60 * 60 * 24)); // convert difference to days
                    //convert to integer
                    $overdue = (int)$overdue;
                    $account->overdue = $overdue;
                    //fine is 15 multiplied by the number of days overdue
                    $amount = 15 * $overdue;
                    $account->date_returned = date('Y-m-d');
                    $account->save();
                    $book->copies = $book->copies + 1;
                    $book->save();

                    $studentId = Auth::user()->student_id;

                    // Call Finance portal API to generate invoice
                    $client = new Client();
                    $response = $client->post('http://localhost:8888/api/generate_invoice/' . $studentId . '/' . $amount);
                    
                    if ($response->getStatusCode() == 201) {
                        $invoice = json_decode($response->getBody());
                        $invoiceId = $invoice->invoice->invoice_ref;
                        
                        return redirect()->route('myaccount')->with('success', 'Thanks for your return. You have been fined $'.$amount.'. Please visit Payment Portal to pay invoice reference number: ' . $invoiceId);
                    } else {
                        return response()->json(['message' => 'Failed to Return Book'], 400);
                    }
                }
                $account->date_returned = date('Y-m-d');
                $account->save();
            } else {
                return redirect()->back()->with('error', 'No record found');
            }

            $book->copies = $book->copies + 1;
            $book->save();
            return redirect()->route('myaccount')->with('success', 'Thank you for returning the book.');
        }else{
            return redirect()->back()->with('error', 'Book not found, Invalid ISBN');
        }
    }

    public function myaccount(){
        if(empty(auth()->user()->id)){
            return redirect()->route('welcome');
        }
        $accounts = Account::where('student_id', Auth::user()->student_id)->get();
        return view('myaccount', ['accounts' => $accounts]);
    }
}
