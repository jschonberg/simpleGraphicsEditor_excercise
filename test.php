<?php
class GraphicsEditor{
    private $image = NULL;
    private $cols = 0;
    private $rows = 0;

    function colRowCheck($col, $row){
        if(!is_numeric($col) or !is_numeric($row)){
            echo "ERROR:all columns and rows must be specified by a number\n";
            return;
        }
        if($row <= 0 or $row > $this->rows){
            echo "ERROR:row $row is out of range\n";
            return;
        }
        if($col <= 0 or $col > $this->cols){
            echo "ERROR:column $col is out of range\n";
            return;
        }

        return 1;
    }

    function colorCheck($color){
        if(!preg_match("/^[A-Z]$/",$color)){
            echo "ERROR: color \"$color\" invalid.  All colors must be a single capital letter\n";
            return;
        }

        return 1;
    }

    function createImage($num_cols, $num_rows){
        //If already created, ask user to confirm
        if(isset($this->image)){
            echo "This will erase your current $this->cols X $this->rows image\n";
            echo "Are you sure you wish to proceed? [Y/N]?\n";
            $response = trim(fgets(STDIN));
            if(!($response == 'Y' or $response == 'y')){
                echo "Task cancelled by user.\n";
                return;
            }
        }

        //Create the new image
        $this->cols = $num_cols;
        $this->rows = $num_rows;
        $this->image = array_fill(0, $num_cols, array_fill(0, $num_rows, 'O'));
    }

    function clearImage(){
        $this->image = array_fill(0, $this->cols, array_fill(0, $this->rows, 'O'));
    }

    function colorPixel($col, $row, $color){
        //Some basic sanity checks
        if(!$this->colRowCheck($col,$row) or !$this->colorCheck($color)){
            return;
        }

        //Paint the pixel
        $this->image[$col-1][$row-1] = $color;
    }

    function drawColumn($col, $start_row, $end_row, $color){
        //Some basic sanity checks
        if(!$this->colRowCheck($col,$start_row) or !$this->colRowCheck($col,$end_row) or !$this->colorCheck($color)){
            return;
        }
        if($end_row < $start_row){
            $temp = $end_row;
            $end_row = $start_row;
            $start_row = $temp;
        }

        //Paint Column
        for ($i=($start_row-1); $i < $end_row; $i++) { 
            $this->image[$col-1][$i] = $color;
        }

    }

    function drawRow($row, $start_col, $end_col, $color){
        //Some basic sanity checks
        if(!$this->colRowCheck($start_col, $row) or !$this->colRowCheck($end_col, $row) or !$this->colorCheck($color)){
            return;
        }
        if($end_col < $start_col){
            $temp = $end_col;
            $end_col = $start_col;
            $start_col = $temp;
        }

        //Paint Row
        for ($i=($start_col-1); $i < $end_col; $i++) { 
            $this->image[$i][$row-1] = $color;
        }        

    }

    function fill($col, $row, $new_color){
        //Some basic sanity checks
        if(!$this->colRowCheck($col,$row) or !$this->colorCheck($new_color)){
            return;
        }

        //Using a basic depth-first search approach, implemented non-recursively
        $investigated_cell = array_fill(0, $this->cols, array_fill(0, $this->rows, FALSE));
        $stack = new SplStack();
        $old_color = $this->image[$col-1][$row-1];
        
        $stack->push(array($col-1, $row-1)); //0-indexed from here on out
        while(!$stack->isEmpty()){
            $focus_cell = $stack->pop();
            $focus_col = $focus_cell[0];
            $focus_row = $focus_cell[1];

            $this->image[$focus_col][$focus_row] = $new_color;
            $investigated_cell[$focus_col][$focus_row] = TRUE;

            //Check above
            $above_row = $focus_row - 1;
            if($above_row>=0 and !$investigated_cell[$focus_col][$above_row] and $this->image[$focus_col][$above_row]==$old_color){
                $stack->push(array($focus_col, $above_row));
            }
            //Check below
            $below_row = $focus_row + 1;
            if($below_row<$this->rows and !$investigated_cell[$focus_col][$below_row] and $this->image[$focus_col][$below_row]==$old_color){
                $stack->push(array($focus_col, $below_row));
            }
            //Check right
            $right_col = $focus_col + 1;
            if($right_col<$this->cols and !$investigated_cell[$right_col][$focus_row] and $this->image[$right_col][$focus_row]==$old_color){
                $stack->push(array($right_col, $focus_row));
            }
            //Check left
            $left_col = $focus_col - 1;
            if($left_col>=0 and !$investigated_cell[$left_col][$focus_row] and $this->image[$left_col][$focus_row]==$old_color){
                $stack->push(array($left_col,$focus_row));
            }
        }
    }

    function show(){
        for($i=0; $i < $this->rows; $i++) { 
            for ($j=0; $j < $this->cols; $j++) { 
                echo $this->image[$j][$i];
            }
            echo "\n";
        }
    }

    function terminate(){
        exit("\n....Goodbye....\n\n");
    }

    function getInput(){
        echo "Please enter a command:";
        $command = preg_split('/\s+/', trim(fgets(STDIN)));
        if($command[0] == "I"){
            if(count($command) != 3){
                echo "ERROR: you have entered an insufficient number of arguments for the \"I\" command\n";
                return;
            }
            $num_cols = $command[1];
            $num_rows = $command[2];
            if($num_cols < 1 or $num_cols > 250){
                echo "ERROR: the number of columns must be between 1 and 250\n";
                echo "You entered $num_cols\n";
                return;
            }
            if($num_rows < 1 or $num_rows > 250){
                echo "ERROR: the number of rows must be between 1 and 250\n";
                echo "You entered $num_rows\n";
                return;
            }
            $this->createImage($num_cols, $num_rows);
        }elseif ($command[0] == "C") {
            if(!isset($this->image)){
                echo "ERROR: there is no image to clear. You must create an image using the \"I\" command first\n";
                return;
            }
            $this->clearImage();
        }elseif ($command[0] == "L") {
            if(count($command) != 4){
                echo "ERROR: you have entered an insufficient number of arguments for the \"L\" command\n";
                return;
            }
            if(!isset($this->image)){
                echo "ERROR: you must create an image using the \"I\" command before you can color any pixels\n";
                return;
            }
            $this->colorPixel($command[1],$command[2], $command[3]);
        }elseif ($command[0] == "V") {
            if(count($command) != 5){
                echo "ERROR: you have entered an insufficient number of arguments for the \"V\" command\n";
                return;
            }
            if(!isset($this->image)){
                echo "ERROR: you must create an image using the \"I\" command before you can draw any lines\n";
                return;
            }
            $this->drawColumn($command[1], $command[2], $command[3], $command[4]);
        }elseif ($command[0] == "H") {
            if(count($command) != 5){
                echo "ERROR: you have entered an insufficient number of arguments for the \"H\" command\n";
                return;
            }
            if(!isset($this->image)){
                echo "ERROR: you must create an image using the \"I\" command before you can draw any lines\n";
                return;
            }
            $this->drawRow($command[3], $command[1], $command[2], $command[4]);
        }elseif ($command[0] == "F") {
            if(count($command) != 4){
                echo "ERROR: you have entered an insufficient number of arguments for the \"F\" command\n";
                return;
            }
            if(!isset($this->image)){
                echo "ERROR: you must create an image using the \"I\" command before you can fill any space\n";
                return;
            }
            $this->fill($command[1], $command[2], $command[3]);
        }elseif ($command[0] == "S") {
            if(!isset($this->image)){
                echo "ERROR: there is no image to show. You must create an image using the \"I\" command first\n";
                return;
            }
            $this->show();
        }elseif ($command[0] == "X") {
            $this->terminate();
        }
        else{
            echo "ERROR: invalid command \"$command[0]\". Please try again\n";
        }
    }
}

//Run the program
echo "#########################################\n";
echo "#########################################\n";
echo "Welcome to the Schonberg Graphical Editor\n";
echo "#########################################\n";
echo "#########################################\n";
echo "\n";
$image = new GraphicsEditor();
while(1){
    $image->getInput();
}

?>